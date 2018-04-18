<?php

namespace App\Http\Controllers\Portal;

use App\Notification;
use Illuminate\Http\Request;
use TCG\Voyager\Facades\Voyager;
use Illuminate\Support\Facades\DB;
use TCG\Voyager\Database\Schema\SchemaManager;
use TCG\Voyager\Http\Controllers\VoyagerBreadController as BaseVoyagerBreadController;

class NotificationController extends BaseVoyagerBreadController
{

    public function index(Request $request)
    {
        // GET THE SLUG, ex. 'posts', 'pages', etc.
        $slug = $this->getSlug($request);

        // GET THE DataType based on the slug
        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        $this->authorize('browse', app($dataType->model_name));

        $getter = $dataType->server_side ? 'paginate' : 'get';

        $search = (object) ['value' => $request->get('s'), 'key' => $request->get('key'), 'filter' => $request->get('filter')];
        $searchable = $dataType->server_side ? array_keys(SchemaManager::describeTable(app($dataType->model_name)->getTable())->toArray()) : '';
        $orderBy = $request->get('order_by');
        $sortOrder = $request->get('sort_order', null);

        // Next Get or Paginate the actual content from the MODEL that corresponds to the slug DataType
        if (strlen($dataType->model_name) != 0) {
            $model = app($dataType->model_name);
            $query = $model::select('*');

            $relationships = $this->getRelationships($dataType);

            // If a column has a relationship associated with it, we do not want to show that field
            $this->removeRelationshipField($dataType, 'browse');

            if ($search->value && $search->key && $search->filter) {
                $search_filter = ($search->filter == 'equals') ? '=' : 'LIKE';
                $search_value = ($search->filter == 'equals') ? $search->value : '%'.$search->value.'%';
                $query->where($search->key, $search_filter, $search_value);
            }

            if ($orderBy && in_array($orderBy, $dataType->fields())) {
                $querySortOrder = (!empty($sortOrder)) ? $sortOrder : 'DESC';
                $dataTypeContent = call_user_func([
                    $query->with($relationships)->orderBy($orderBy, $querySortOrder),
                    $getter,
                ]);
            } elseif ($model->timestamps) {
                $dataTypeContent = call_user_func([$query->latest($model::CREATED_AT), $getter]);
            } else {
                $dataTypeContent = call_user_func([$query->with($relationships)->orderBy($model->getKeyName(), 'DESC'), $getter]);
            }

            // Replace relationships' keys for labels and create READ links if a slug is provided.
            $dataTypeContent = $this->resolveRelations($dataTypeContent, $dataType);
        } else {
            // If Model doesn't exist, get data from table name
            $dataTypeContent = call_user_func([DB::table($dataType->name), $getter]);
            $model = false;
        }

        // Check if BREAD is Translatable
        if (($isModelTranslatable = is_bread_translatable($model))) {
            $dataTypeContent->load('translations');
        }
        // dd($dataType);

        // Check if server side pagination is enabled
        $isServerSide = isset($dataType->server_side) && $dataType->server_side;

        // $view = 'voyager::bread.browse';
        //
        // if (view()->exists("voyager::$slug.browse")) {
        //     $view = "voyager::$slug.browse";
        // }

        $view = 'notifications.browse';

        return Voyager::view($view, compact(
            'dataType',
            'dataTypeContent',
            'isModelTranslatable',
            'search',
            'orderBy',
            'sortOrder',
            'searchable',
            'isServerSide'
        ));
    }

    public function showNotifications($slug) {
      dd($slug);
      return 'ahoj' . $slug;
    }

    public function showNotificationMessage(Request $request, $datatableSlug, $uuid)
    {
        $data = DB::table($datatableSlug)->where('uuid', $uuid)->get();
        $subject = null;
        if (isset($data[0])) {
          $data = $data[0];
          $is_read = $data->is_read;
          unset($data->id);
          unset($data->uuid);
          unset($data->notification_id);
          unset($data->is_read);
          $subject = $data->Subject;
          unset($data->Subject);
        }

        $view = 'notifications.read';

        return Voyager::view($view, compact('data', 'subject', 'uuid', 'datatableSlug', 'is_read'));
    }

    public function markAsRead($slug, $uuid) {
      DB::table($slug)->where('uuid', $uuid)->update(['is_read' => 1]);

      return redirect()->back();
    }

    public function getNotifications() {
      $wqs = Notification::all();
      $msgs = [];
      foreach ($wqs as $wq) {
        $msgs[$wq->value('slug_datatable_name')] = DB::table(
          $wq->value('slug_datatable_name'))
          ->where([
            'notification_id' => $wq->value('id'),
            'is_read' => false])
            ->get();
      }
      return $msgs;
    }

    public function getNotificationsNumber($msgs) {
      $number = 0;
      foreach ($msgs as $wq) {
        $number += count($wq);
      }
      return $number;
    }

    public function getSlug(Request $request)
    {
        return 'notifications';
    }
}
