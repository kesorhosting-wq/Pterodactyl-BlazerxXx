@extends('layouts.admin')
<?php 
    // Define extension information.
    $EXTENSION_ID = "subdomains";
    $EXTENSION_NAME = stripslashes("SubDomains");
    $EXTENSION_VERSION = "0.4.1";
    $EXTENSION_DESCRIPTION = stripslashes("Allow users to connect with minecraft via Subdomain instead of ip+port");
    $EXTENSION_ICON = "/assets/extensions/subdomains/icon.jpg";
    $EXTENSION_WEBSITE = "[website]";
    $EXTENSION_WEBICON = "[webicon]";
?>
@include('blueprint.admin.template')

@section('title')
    {{ $EXTENSION_NAME }}
@endsection

@section('content-header')
    @yield('extension.header')
@endsection

@section('content')
    @yield('extension.config')
    @yield('extension.description')  <div>
  <script>
      // Show save button upon form input changes.
      document.addEventListener("DOMContentLoaded", function() {
          showSaveButton()
      });

      function showSaveButton() {
          const __identifier___configForm = document.getElementById("config-form");
          const __identifier___saveOverlay = document.getElementById("save-overlay");

          __identifier___configForm.addEventListener("change", function() {
              __identifier___saveOverlay.style.display = "inline";
              setTimeout(() => {
                  __identifier___saveOverlay.style.bottom = "10px";
              }, 100)
          });
      }
  </script>

  <div class="row">
      <div class="col-xs-12 col-md-8">
          <div class="box box-primary">
              <div class="box-header with-border">
                  <h3 class="box-title">Domains</h3>
              </div>
              <div class="box-body table-responsive no-padding">
                  <table class="table table-hover">
                      <thead>
                          <tr>
                              <th>ID</th>
                              <th>Domain</th>
                              <th></th>
                          </tr>
                      </thead>
                      <tbody>
                          @foreach ($domains as $domain)
                              <tr>
                                  <td>{{ $domain->id }}</td>
                                  <td>
                                      {{ $domain->domain }}
                                  </td>
                                  <td>
                                      <button class="btn btn-danger btn-xs" data-action="delete"
                                          data-id="{{ $domain->id }}">
                                          <i class="fa fa-trash"></i>
                                      </button>
                                  </td>
                              </tr>
                          @endforeach
                      </tbody>
                  </table>
              </div>
          </div>
          <div class="box box-info">
              <div class="box-header with-border">
                  <h3 class="box-title">Sub Domains</h3>
              </div>
              <div class="box-body table-responsive no-padding">
                  <table class="table table-hover">
                      <thead>
                          <tr>
                              <th>ID</th>
                              <th>Sub Domain</th>
                              <th>Domain</th>
                              <th>Server</th>
                              <th></th>
                          </tr>
                      </thead>
                      <tbody>
                          @foreach ($domains as $domain)
                              @foreach ($domain->subdomains as $subdomain)
                                  <tr>
                                      <td>{{ $subdomain->id }}</td>
                                      <td>
                                          {{ $subdomain->subdomain }}
                                      </td>
                                      <td>
                                          {{ $domain->domain }}
                                      </td>
                                      <td>
                                          <a href="{{ route('admin.servers.view', $subdomain->server->id) }}">
                                              {{ $subdomain->server->name }}
                                          </a>
                                      </td>
                                      <td>
                                          <button class="btn btn-danger btn-xs" data-action="deletes"
                                              data-id="{{ $subdomain->id }}">
                                              <i class="fa fa-trash"></i>
                                          </button>
                                      </td>
                                  </tr>
                              @endforeach
                          @endforeach
                      </tbody>
                  </table>
              </div>
          </div>
      </div>
      <div class="col-xs-12 col-md-4">
          <div class="box box-success" id="createBox">
              <div class="box-header with-border">
                  <h3 class="box-title">Add new domain</h3>
              </div>
              <form method="post">
                  <input type="hidden" name="action" value="create">
                  <div class="box-body">
                      <div class="form-group">
                          <label for="domain">Domain</label>
                          <input type="text" class="form-control" id="domain" name="domain" placeholder="Domain"
                              required>
                      </div>
                      {{ csrf_field() }}
                      <button type="submit" class="btn btn-success pull-right">Create</button>
                  </div>
              </form>
          </div>

          <div class="box box-danger" id="createBox">
            <div class="box-header with-border">
                <h3 class="box-title">Edit API Key</h3>
                <p>Update your Cloudflare API Key and Email</p>
            </div>
            <form method="post">
                <input type="hidden" name="action" value="api-key">
                <div class="box-body">
                    <div class="form-group">
                        <label for="api_key">API Key</label>
                        <input type="text" class="form-control" id="api_key" name="api_key" placeholder="API Key"
                            required>
                    </div>
                    <div class="form-group">
                        <label for="email">Cloudflare Email</label>
                        <input type="text" class="form-control" id="email" name="email" placeholder="Email"
                            required>
                    </div>                    
                    {{ csrf_field() }}
                    <button type="submit" class="btn btn-success pull-right">Update</button>
                </div>
            </form>
        </div>
      </div>
  </div>
  @section('footer-scripts')
@parent
  <script>
      $(document).ready(function() {
        console.log('ready');
          $('[data-action="delete"]').click(function(event) {
              event.preventDefault();
              var self = $(this);
              swal({
                  title: '',
                  type: 'warning',
                  text: 'Are you sure that you want to delete this domain?',
                  showCancelButton: true,
                  confirmButtonText: 'Delete',
                  confirmButtonColor: '#d9534f',
                  closeOnConfirm: false,
                  showLoaderOnConfirm: true,
              }, function() {
                  $.ajax({
                      method: 'POST',
                      url: window.location.pathname,
                      headers: {
                          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content'),
                      },
                      data: {
                          action: 'delete',
                          domain_id: self.data('id'),
                      },
                  }).done(function() {
                      self.parent().parent().slideUp();
                      swal({
                          type: 'success',
                          title: '',
                          text: 'Domain has been deleted.',
                          timer: 1000,
                          showConfirmButton: false,
                      });
                  }).fail(function(jqXHR) {
                      console.error(jqXHR);
                      swal({
                          title: 'Whoops!',
                          text: jqXHR.responseJSON.error,
                          type: 'error',
                      });
                  });
              });
          });
      });

      $(document).ready(function() {
          $('[data-action="deletes"]').click(function(event) {
              event.preventDefault();
              var self = $(this);
              swal({
                  title: '',
                  type: 'warning',
                  text: 'Are you sure that you want to delete this subdomain?',
                  showCancelButton: true,
                  confirmButtonText: 'Delete',
                  confirmButtonColor: '#d9534f',
                  closeOnConfirm: false,
                  showLoaderOnConfirm: true,
              }, function() {
                  $.ajax({
                      method: 'POST',
                      // curent url
                      url: window.location.pathname,
                      headers: {
                          'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content'),
                      },
                      data: {
                          action: 'subdomain-delete',
                          subdomain_id: self.data('id'),
                      },
                  }).done(function() {
                      self.parent().parent().slideUp();
                      swal({
                          type: 'success',
                          title: '',
                          text: 'Domain has been deleted.',
                          timer: 1000,
                          showConfirmButton: false,
                      });
                  }).fail(function(jqXHR) {
                      console.error(jqXHR);
                      swal({
                          title: 'Whoops!',
                          text: jqXHR.responseJSON.error,
                          type: 'error',
                      });
                  });
              });
          });
      });
  </script>
  @endsection
  </div>
@endsection
