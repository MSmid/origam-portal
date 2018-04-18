@extends('voyager::master')

@section('page_title', __('voyager.generic.view').' '.'Notification')

@section('page_header')
    <h1 class="page-title">
        <i class="voyager-mail"></i> {{ __('voyager.generic.viewing') }} {{ 'Notification' }} &nbsp;
        @if ($is_read)
          <span class="btn btn-warning"><i class="glyphicon glyphicon-ok"></i></span>
        @else
          <a href="{{ route('portal.notifications.mark', ['slug' => $datatableSlug, 'uuid' => $uuid]) }}" class="btn btn-warning">
              <i class="glyphicon glyphicon-ok"></i>&nbsp;
              <span>{{ __('origam_portal.generic.mark_as_read') }}</span>
          </a>
        @endif
        <a href="{{ route('voyager.dashboard') }}" class="btn btn-primary">
            <i class="voyager-angle-left"></i> <span>{{ __('origam_portal.generic.back') }}</span>
        </a>
    </h1>
    @include('voyager::multilingual.language-selector')
@stop

@section('content')
    <div class="page-content read container-fluid">
        <div class="row">
            <div class="col-md-12">

                <div class="panel panel-bordered" style="padding-bottom:5px;">
                    <!-- form start -->
                    @if (isset($subject))
                      <div class="panel-heading" style="border-bottom: 0">
                          <h3 class="panel-title">{{ 'Subject' }}</h3>
                      </div>
                      <div class="panel-body" style="padding-top: 0">
                          <p>{{$subject}}</p>
                      </div>
                      <hr style="margin: 0"/>
                    @endif
                    @foreach ($data as $key => $value)
                      <div class="panel-heading" style="border-bottom: 0">
                          <h3 class="panel-title">{{ $key }}</h3>
                      </div>
                      <div class="panel-body" style="padding-top: 0">
                          <p>{{$value}}</p>
                      </div>
                      <hr style="margin: 0"/>
                    @endforeach


                </div>
            </div>
        </div>
    </div>
@stop

{{-- @section('javascript')
    @if ($isModelTranslatable)
    <script>
        $(document).ready(function () {
            $('.side-body').multilingual();
        });
    </script>
    <script src="{{ voyager_asset('js/multilingual.js') }}"></script>
    @endif
@stop --}}
