{{-- Pterodactyl - Panel --}}
{{-- Copyright (c) 2015 - 2017 Dane Everitt <dane@daneeveritt.com> --}}

{{-- This software is licensed under the terms of the MIT license. --}}
{{-- https://opensource.org/licenses/MIT --}}
@extends('layouts.master')

@section('title')
    Mod Manager
@endsection

@section('content-header')
    <h1>Mod Manager</h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('index') }}">@lang('strings.home')</a></li>
        <li><a href="{{ route('server.index', $server->uuidShort) }}">{{ $server->name }}</a></li>
        <li>@lang('navigation.server.configuration')</li>
        <li class="active">Mod Manager</li>
    </ol>
@endsection

@section('content')
    <div class="row">
        @if(count($categories) > 0)
            @foreach($categories as $key => $category)
                <div class="col-xs-12 col-md-6">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title mt-3">{{$category}}</h3>
                        </div>
                        <div class="box-body table-responsive no-padding">
                            <table class="table table-hover">
                                <tbody>
                                <tr>
                                    <th>Name</th>
                                    <th>Version</th>
                                    <th>Description</th>
                                    <th>Action</th>
                                </tr>
                                @foreach($mods[$key] as $key2 => $mod)
                                    <tr>
                                        <td>{{$mod->name}}</td>
                                        <td><code>{{$mod->version}}</code></td>
                                        <td>{{$mod->description}}</td>
                                        <td>
                                            @if($mod->installed != true)
                                                <button class="btn btn-sm btn-success" onclick="install({{$mod->id}});" id="action-{{$mod->id}}"><i class="fa fa-download"></i> Install</button>
                                            @else
                                                <button class="btn btn-sm btn-danger" onclick="uninstall({{$mod->id}})" id="action-{{$mod->id}}"><i class="fa fa-trash"></i> Uninstall</button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="col-xs-12">
                <div class="alert alert-info alert-dismissable" role="alert">
                    No mod available for this server!
                </div>
            </div>
        @endif
    </div>
@endsection

@section('footer-scripts')
    @parent
    {!! Theme::js('js/frontend/server.socket.js') !!}
    <script>
        function install(id) {
            swal({
                type: 'warning',
                title: 'Install Mod',
                text: 'Do you want to install this mod?',
                showCancelButton: true,
                showConfirmButton: true,
                closeOnConfirm: true,
            }, function () {
                $('#action-' + id).html('<i class=\'fa fa-spinner fa-spin \'></i> Installing...');

                $.ajax({
                    method: 'POST',
                    url: '/server/{{$server->uuidShort}}/mods/install',
                    headers: {'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')},
                    data: {
                        mod_id: id
                    }
                }).done(function (data) {
                    if (data.success === true) {
                        swal.close();

                        $('#action-' + id).html('<i class="fa fa-trash"></i> Uninstall').removeAttr('onclick').addClass('btn-danger').removeClass('btn-success').attr('onclick', 'uninstall(' + id + ');');
                    } else {
                        $('#action-' + id).html(' <i class="fa fa-download"></i> Install');

                        setTimeout(function() {
                            swal({
                                type: 'error',
                                title: 'Ooops!',
                                text: (typeof data.error !== 'undefined') ? data.error : 'Couldn\'t search! Please try again later...'
                            });
                        }, 10);
                    }
                }).fail(function (jqXHR) {
                    $('#action-' + id).html('<i class="fa fa-download"></i> Install');

                    setTimeout(function() {
                        swal({
                            type: 'error',
                            title: 'Ooops!',
                            text: (typeof jqXHR.responseJSON.error !== 'undefined') ? jqXHR.responseJSON.error : 'A system error has occurred! Please try again later...'
                        });
                    }, 10);
                });
            });
        }

        function uninstall(id) {
            swal({
                type: 'warning',
                title: 'Uninstall Mod',
                text: 'Do you want to uninstall this mod?',
                showCancelButton: true,
                showConfirmButton: true,
                closeOnConfirm: false,
            }, function () {
                $('#action-' + id).html('<i class=\'fa fa-spinner fa-spin \'></i> Uninstalling...');

                $.ajax({
                    method: 'POST',
                    url: '/server/{{$server->uuidShort}}/mods/uninstall',
                    headers: {'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')},
                    data: {
                        mod_id: id
                    }
                }).done(function (data) {
                    if (data.success === true) {
                        swal.close();

                        $('#action-' + id).html('<i class="fa fa-download"></i> Install').removeAttr('onclick').addClass('btn-success').removeClass('btn-danger').attr('onclick', 'install(' + id + ');');
                    } else {
                        $('#action-' + id).html(' <i class="fa fa-trash"></i> Uninstall');

                        setTimeout(function() {
                            swal({
                                type: 'error',
                                title: 'Ooops!',
                                text: (typeof data.error !== 'undefined') ? data.error : 'Couldn\'t search! Please try again later...'
                            });
                        }, 10);
                    }
                }).fail(function (jqXHR) {
                    $('#action-' + id).html('<i class="fa fa-trash"></i> Uninstall');

                    setTimeout(function() {
                        swal({
                            type: 'error',
                            title: 'Ooops!',
                            text: (typeof jqXHR.responseJSON.error !== 'undefined') ? jqXHR.responseJSON.error : 'A system error has occurred! Please try again later...'
                        });
                    }, 10);
                });
            });
        }
    </script>
@endsection
