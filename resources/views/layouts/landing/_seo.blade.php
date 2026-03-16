@php
    $title = $metaData['meta_title']?->value ?? 'Fox Delivery';
    $description = $metaData['meta_description']?->value ?? 'Fox Delivery: peça comida, mercado, farmácia e muito mais sem sair de casa.';
    $image = \App\CentralLogics\Helpers::get_full_url(
        'landing/meta_image',
        $metaData['meta_image']?->value ?? '',
        $metaData['meta_image']?->storage[0]?->value ?? 'public',
        'upload_image'
    );
    $url = url()->current();
@endphp

<meta name="description" content="{{ $description }}">
<meta name="robots" content="index,follow">
<meta name="author" content="Fox Delivery">
<link rel="canonical" href="{{ $url }}">

<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:image" content="{{ $image }}">
<meta property="og:url" content="{{ $url }}">
<meta property="og:type" content="website">
<meta property="og:site_name" content="Fox Delivery">
<meta property="og:locale" content="pt_BR">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $image }}">

<meta name="theme-color" content="#E11D2E">
