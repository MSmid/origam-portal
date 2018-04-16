<?php

namespace App\Http\Controllers\Portal;

use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Events\BreadDataDeleted;
use TCG\Voyager\Http\Controllers\VoyagerBreadController as BaseVoyagerBreadController;

class VoyagerBreadController extends BaseVoyagerBreadController
{
    public function destroy(Request $request, $id)
    {
        $slug = $this->getSlug($request);

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        $this->authorize('delete', app($dataType->model_name));

        // Init array of IDs
        $ids = [];
        if (empty($id)) {
            // Bulk delete, get IDs from POST
            $ids = explode(',', $request->ids);
        } else {
            // Single item delete, get ID from URL or Model Binding
            $ids[] = $id instanceof Model ? $id->{$id->getKeyName()} : $id;
        }
        foreach ($ids as $id) {
            $data = call_user_func([$dataType->model_name, 'findOrFail'], $id);
            $this->cleanup($dataType, $data);
        }

        $displayName = count($ids) > 1 ? $dataType->display_name_plural : $dataType->display_name_singular;

        $res = $data->destroy($ids);
        $this->dropSyncTable($data->value('entity_name'));

        $data = $res
            ? [
                'message'    => __('voyager.generic.successfully_deleted')." {$displayName}",
                'alert-type' => 'success',
            ]
            : [
                'message'    => __('voyager.generic.error_deleting')." {$displayName}",
                'alert-type' => 'error',
            ];

        if ($res) {
            event(new BreadDataDeleted($dataType, $data));
        }

        return redirect()->route("voyager.{$dataType->slug}.index")->with($data);
    }

    public function dropSyncTable($tableName) {
      Schema::dropIfExists($tableName);
    }
}
