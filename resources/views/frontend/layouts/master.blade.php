<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $basic_settings->sitename(__($page_title??'')) }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    @include('partials.header-asset')

    @stack('css')
</head>
<body class="{{ selectedLangDir() ?? "ltr"}}">

@include('frontend.partials.preloader')
@php
    $class = "";
    if(!Route::is("index","merchant")) {
        $class = "others";
    }
@endphp 
@include('frontend.partials.header',[
    'class'     => $class,
])

@yield("content")
@include('frontend.partials.scroll-to-top')
@include('frontend.partials.download-app')
@include('frontend.partials.footer')
@include('partials.footer-asset')


<script>
    // jvectormap JS
    var colors = ["#0071AF"],
        dataColors = $("#world-map-markers").data("colors");
    function hexToRGB(a, e) {
        var t = parseInt(a.slice(1, 3), 16),
            o = parseInt(a.slice(3, 5), 16),
            n = parseInt(a.slice(5, 7), 16);
        return e ? "rgba(" + t + ", " + o + ", " + n + ", " + e + ")" : "rgb(" + t + ", " + o + ", " + n + ")";
    }
    dataColors && (colors = dataColors.split(",")),
    $("#world-map-markers").vectorMap({
        map: "world_mill_en",
        normalizeFunction: "polynomial",
        hoverOpacity: 0.7,
        hoverColor: !1,
        zoomOnScroll: false,
        regionStyle: { initial: { fill: "#d1dbe5" } },
        markerStyle: { initial: { r: 9, fill: colors[0], "fill-opacity": 0.9, stroke: "#fff", "stroke-width": 7, "stroke-opacity": 0.4 }, hover: { stroke: "#fff", "fill-opacity": 1, "stroke-width": 1.5 } },
        backgroundColor: "transparent",
    });
</script>

</body>
</html>
