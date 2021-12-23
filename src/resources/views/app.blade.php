<?php  
    $link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $layout = '';    
    if (stripos($link, 'admin') !== FALSE) {
        $layout = '.master'; 
    } else if (stripos($link, 'corp') !== FALSE) {
        $layout = '.corp.master'; 
    }
?>

@extends('layout'.$layout)
@section('head')
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/@mdi/font@4.x/css/materialdesignicons.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/@mdi/font@5.x/css/materialdesignicons.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, minimal-ui">
        <link href="https://unpkg.com/bootstrap@4.5.2/dist/css/bootstrap.min.css" rel="stylesheet" />
        <link href="https://unpkg.com/bootstrap-vue@2.16.0/dist/bootstrap-vue.css" rel="stylesheet" />
        
        <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/4.0.1/socket.io.js"></script> -->
@endsection

@section('content')
    <div id="marketplace-integration"></div>
    <script src="{{ elixir('vendor/codificar/marketplace-integration/js/app.js') }}"></script>
@endsection
