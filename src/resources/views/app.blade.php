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
        <link href="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/vuetify@2.x/dist/vuetify.min.css" rel="stylesheet">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, minimal-ui">
        <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/4.0.1/socket.io.js"></script> -->
@endsection

@section('content')
    <div id="marketplace-integration"></div>
    <script src="/marketplace-integration/lang.trans/settings,zedelivery"> </script> 
    <script src="/marketplace-integration/js/env.js"> </script> 
    <script src="{{ asset('vendor/codificar/marketplace-integration/js/marketplace-integration.js') }}"></script>
@endsection
