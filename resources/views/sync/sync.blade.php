@extends('voyager::master')

@section('page_title', __('origam_portal.generic.syncing').' '.__('voyager.generic.database'))

@section('page_header')
    <h1 class="page-title">
        <i class="voyager-data"></i> {{ $pageData->name }}
    </h1>
    <i>{{  $pageData->url }}</i>
    {{-- AJAX --}}
    {{-- <button type="submit" id="check-action" class="btn btn-success">
        <span>{{ __('origam_portal.generic.check') }}</span>
    </button> --}}
@stop

@section('content')

    <div class="page-content container-fluid">
        @if (!$pageData->is_synced)
          <form action="{{ route('portal.synchronization.check', $pageData->id) }}" method="POST" id="check-action" class="sync-action" style="display: inline-block">
              {{ csrf_field() }}
              <button type="submit" class="btn btn-success">
                  <span>{{ __('origam_portal.generic.check') }}</span>
              </button>
          </form>
        @endif
        @if ($pageData->is_synced)
          <form action="{{ route('portal.synchronization.syncStart', $pageData->id) }}" method="POST" id="start-action" class="sync-action" style="display: inline-block">
              {{ csrf_field() }}
              <button type="submit" class="btn btn-success">
                  <span>{{ __('origam_portal.generic.start') }}</span>
              </button>
          </form>
        @endif
        @include('voyager::alerts')
        <div class="progress hidden">
          <div class="progress-bar progress-bar-striped active progress-bar-info" role="progressbar" style="width: 100%"></div>
        </div>

        <div class="row content-result">
            <div class="col-md-12">
              @if ($data)
                @if(isset($data['data']))
                  <h2 class="page-subtitle">Fetched Data</h2>
                  <textarea id="json-result-data" class="resizable-editor" data-editor="json" name="data_result">
                    {{ $data['data'] }}
                  </textarea>
                  <div style="padding-top: 15px">
                    <a href="{{ route('portal.synchronization.create', $pageData->id) }}" class="btn btn-success btn-add-new">
                        <i class="voyager-plus"></i> <span>{{ __('voyager.generic.add_new') }}</span>
                    </a>
                  </div>
                @endif
                @if(isset($data['error']))
                  <h2 class="page-subtitle">Error</h2>
                  <pre>{{ $data['error'] }}</pre>
                @endif
                @if(isset($data['result']))
                  <h2 class="page-subtitle">Result</h2>
                  <div class="panel-body">
                    <pre>{{ $data['result'] }}</pre>
                  </div>
                @endif
              @endif
        </div>
    </div>

    <div class="modal modal-danger fade" tabindex="-1" id="delete_builder_modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('voyager.generic.close') }}"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="voyager-trash"></i>  {!! __('voyager.database.delete_table_bread_quest', ['table' => '<span id="delete_builder_name"></span>']) !!}</h4>
                </div>
                <div class="modal-footer">
                    <form action="{{ route('voyager.database.bread.delete', ['id' => null]) }}" id="delete_builder_form" method="POST">
                        {{ method_field('DELETE') }}
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="submit" class="btn btn-danger" value="{{ __('voyager.database.delete_table_bread_conf') }}">
                    </form>
                    <button type="button" class="btn btn-outline pull-right" data-dismiss="modal">{{ __('voyager.generic.cancel') }}</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <div class="modal modal-danger fade" tabindex="-1" id="delete_modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('voyager.generic.close') }}"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="voyager-trash"></i> {!! __('voyager.database.delete_table_bread_quest', ['table' => '<span id="delete_table_name"></span>']) !!}</h4>
                </div>
                <div class="modal-footer">
                    <form action="#" id="delete_table_form" method="POST">
                        {{ method_field('DELETE') }}
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="submit" class="btn btn-danger pull-right" value="{{ __('voyager.database.delete_table_confirm') }}">
                        <button type="button" class="btn btn-outline pull-right" style="margin-right:10px;"
                                data-dismiss="modal">{{ __('voyager.generic.cancel') }}
                        </button>
                    </form>

                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <div class="modal modal-info fade" tabindex="-1" id="table_info" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('voyager.generic.close') }}"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="voyager-data"></i> @{{ table.name }}</h4>
                </div>
                <div class="modal-body" style="overflow:scroll">
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>{{ __('voyager.database.field') }}</th>
                            <th>{{ __('voyager.database.type') }}</th>
                            <th>{{ __('voyager.database.null') }}</th>
                            <th>{{ __('voyager.database.key') }}</th>
                            <th>{{ __('voyager.database.default') }}</th>
                            <th>{{ __('voyager.database.extra') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="row in table.rows">
                            <td><strong>@{{ row.Field }}</strong></td>
                            <td>@{{ row.Type }}</td>
                            <td>@{{ row.Null }}</td>
                            <td>@{{ row.Key }}</td>
                            <td>@{{ row.Default }}</td>
                            <td>@{{ row.Extra }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline pull-right" data-dismiss="modal">{{ __('voyager.generic.close') }}</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

@stop

@section('javascript')

    <script>
        $(function() {
          $('textarea[data-editor]').each(function () {
              var textarea = $(this),
              mode = textarea.data('editor'),
              editDiv = $('<div>').insertBefore(textarea),
              editor = ace.edit(editDiv[0]),
              _session = editor.getSession(),
              valid = false;
              textarea.hide();

              // Use workers only when needed
              editor.on('focus', function () {
                  _session.setUseWorker(true);
              });
              editor.on('blur', function () {
                  if (valid) {
                      textarea.siblings('.validation-error').hide();
                      _session.setUseWorker(false);
                  } else {
                      textarea.siblings('.validation-error').show();
                  }
              });

              _session.setUseWorker(false);

              editor.setAutoScrollEditorIntoView(true);
              editor.$blockScrolling = Infinity;
              editor.setOption("maxLines", 30);
              editor.setOption("minLines", 4);
              editor.setOption("showLineNumbers", false);
              editor.setTheme("ace/theme/github");
              _session.setMode("ace/mode/json");
              if (textarea.val()) {
                  _session.setValue(JSON.stringify(JSON.parse(textarea.val()), null, 4));
              }
              _session.setMode("ace/mode/" + mode);

          });
        });
        var form = document.querySelector('form.sync-action');
        var btn = form.querySelector('button[type="submit"]');
        btn.addEventListener('click', function(ev){
            if (form.checkValidity()) {
                form.className = 'hidden';
                document.querySelector('.progress.hidden').className = 'progress';
                document.querySelector('.content-result').className = 'hidden';
            } else {
                ev.preventDefault();
            }
        });
        //AJAX
        // var btn = document.querySelector('buttons#check-action');
        // btn.addEventListener('click', function(ev){
        //   ev.preventDefault();
        //   btn.className = 'hidden';
        //   document.querySelector('.progress.hidden').className = 'progress';
        //   var token = $('meta[name="csrf-token"]').attr('content');
        //   $.ajaxSetup({
        //     headers: {
        //       'X-CSRF-Token': token
        //     }
        //   });
        //   $.ajax({
        //     url: "route('portal.synchronization.sync', $pageData->id)",
        //     type: "POST",
        //     contentType: 'application/json; charset=utf-8',
        //     data: {
        //       "_token": token
        //     }
        //   });
        // });
    </script>

    <script>

        var table = {
            name: '',
            rows: []
        };

        new Vue({
            el: '#table_info',
            data: {
                table: table,
            },
        });

        $(function () {

            $('.bread_actions').on('click', '.delete', function (e) {
                id = $(this).data('id');
                name = $(this).data('name');

                $('#delete_builder_name').text(name);
                $('#delete_builder_form')[0].action += '/' + id;
                $('#delete_builder_modal').modal('show');
            });

            $('.database-tables').on('click', '.desctable', function (e) {
                e.preventDefault();
                href = $(this).attr('href');
                table.name = $(this).data('name');
                table.rows = [];
                $.get(href, function (data) {
                    $.each(data, function (key, val) {
                        table.rows.push({
                            Field: val.field,
                            Type: val.type,
                            Null: val.null,
                            Key: val.key,
                            Default: val.default,
                            Extra: val.extra
                        });
                        $('#table_info').modal('show');
                    });
                });
            });

            $('td.actions').on('click', '.delete_table', function (e) {
                table = $(this).data('table');
                if ($(this).hasClass('remove-bread-warning')) {
                    toastr.warning('{{ __('voyager.database.delete_bread_before_table') }}');
                } else {
                    $('#delete_table_name').text(table);

                    $('#delete_table_form')[0].action = '{{ route('voyager.database.destroy', ['database' => '__database']) }}'.replace('__database', table)
                    $('#delete_modal').modal('show');
                }
            });

        });
    </script>

@stop
