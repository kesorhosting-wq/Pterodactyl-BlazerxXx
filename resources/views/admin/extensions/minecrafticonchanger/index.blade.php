@extends('layouts.admin')
<?php 
    // Define extension information.
    $EXTENSION_ID = "minecrafticonchanger";
    $EXTENSION_NAME = stripslashes("Minecraft Icon Changer");
    $EXTENSION_VERSION = "1.0.1";
    $EXTENSION_DESCRIPTION = stripslashes("Allow the user to easily change the icon of their Minecraft server.");
    $EXTENSION_ICON = "/assets/extensions/minecrafticonchanger/icon.jpg";
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
    @yield('extension.description')<?php
  $response = cache()->remember('product-minecrafticonchanger', 30 * 60, function () {
    return @file_get_contents("https://api.2038.buzz/products/minecrafticonchanger", false, stream_context_create([
      'http' => [
        'timeout' => 1
      ]
    ]));
  });

  if (!$response) {
    $version = 'Unknown';
    $providers = [];
    $changelog = [];
  } else {
    $data = json_decode($response, true);

    $version = $data['product']['version'];
    $providers = array_values($data['providers']);
    $changelog = [];

    foreach ($data['changelogs'] as $key => $change) {
      $changelog[] = [
        'version' => $key,
        'text' => $change['content'],
        'created' => $change['created']
      ];
    }
  }

  $nonceIdentifier = '49bf9c30c4c6ab7cc6461634006671c4';
  $nonceIdentifierWithoutReplacement = '%%__NONCE' . '__%%';
?>

<div class="row">
  <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12">
    <div class="box {{ $version !== 'Unknown' ? $version !== "1.0.1" ? 'box-danger' : 'box-primary' : 'box-primary' }}">
      <div class="box-header with-border">
        <h3 class="box-title"><i class='bx bx-git-repo-forked' ></i> Information</h3>
      </div>
      <div class="box-body">
        <p>
          Thank you for purchasing <b>Minecraft Icon Changer</b>! You are currently using version <code>1.0.1</code> (latest version is <code>{{ $version }}</code>).
          If you have any questions or need help, please visit our <a href="https://discord.2038.buzz" target="_blank">Discord</a>.
          <b>{{ $nonceIdentifier === $nonceIdentifierWithoutReplacement ? "This is an indev version of the product!" : "" }}</b>
        </p>

        <div class="table-responsive" style="max-height: 250px; margin-bottom: 10px;">
          <table class="table">
            <thead>
              <tr>
                <th style="width: 10px">Version</th>
                <th style="width: 100px">Date</th>
                <th>Changes</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($changelog as $change)
                <tr>
                  <td style="{{ "1.0.1" === $change['version'] ? 'text-decoration: underline; font-weight: bold;' : '' }}">{{ $change['version'] }}</td>
                  <td>{{ Carbon\Carbon::parse($change['created'])->format('Y-m-d') }}</td>
                  <td style="white-space: pre-wrap;">{{ $change['text'] }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <div class="row">
          @foreach ($providers as $provider)
            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
              <a href="{{ $provider['link'] }}" target="_blank" class="btn btn-primary btn-block"><i class='bx bx-store'></i> {{ $provider['name'] }}</a>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
    <div class="box">
      <div class="box-header with-border">
        <h3 class="box-title"><i class='bx bx-cog'></i> Configuration</h3>
      </div>
      <div class="box-body">
        <form method="post" action="{{ route('admin.extensions.minecrafticonchanger.index') }}">
          {{ csrf_field() }}
          <div class="form-group">    
            <label for="nest_id">Eggs</label>
            <select class="form-control" id="eggs" name="eggs[]" required multiple>
              @foreach(DB::table('eggs')->get() as $egg)
                <option value="{{ $egg->id }}" {{ in_array($egg->id, $eggs) ? 'selected' : '' }}>{{ $egg->name }}</option>
              @endforeach
            </select>
          </div>
          <button type="submit" class="btn btn-primary">Save</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
    <div class="box">
      <div class="box-header with-border">
        <h3 class="box-title"><i class='bx bxs-info-square'></i> Banner</h3>
      </div>
      <div class="box-body">
        <img src="/extensions/minecrafticonchanger/minecrafticonchanger_banner.jpg" class="img-rounded" alt="Banner" style="width: 100%;">
      </div>
    </div>
  </div>
</div>

@endsection

@section('footer-scripts')
@parent
<script>
  $('#eggs').select2();
</script>
@endsection
