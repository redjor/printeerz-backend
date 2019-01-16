@extends('layouts/templateAdmin')

@section('content')

<div class="container">
@if (session('status'))
    <div class="alert alert-success mt-2 mb-2">
        {{ session('status') }}
    </div>
@endif

  	<a href="{{action('ProductController@create')}}"><button type="button" title="Ajout d'un nouveau produit" class="btn btn-primary btn-sm ml-2 mt-2 mb-2" style="float:right"><i class="uikon">add</i> Nouveau produit</button></a>
    <br>
<br>

<table class="datatable table table-striped" >
    <thead>
		<tr>
            <th>Noms</th>
            <th></th>
            <th>Références</th>
            <th>Sexes</th>
            <th>Tailles</th>
            <th>Commentaires</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
@foreach ($products as $product)

    <tr>
    <td>{{ $product->title }}</td>
    @if(isset($product->product_zones["title"]))
        <td>{{ $product->product_zones["title"] }}</td>
    @else
        <td></td>
    @endif
    <td>{{ $product->reference }}</td>
    @if ($product->sexe == 'Homme')
        <td> Homme </td>
    @else
        <td> Femme </td>
    @endif
    <?php ?>
    <td></td>

    <?php $description = $product->description;
    if(strlen($description) > 50){
        $description = substr($description, 0, 50);
        $description .= '...';
    }
    ?>
    @if(strlen($description) != 0)
    <td>{{ $description }}</td>
    @else
    <td>{{ '...' }}</td>
    @endif
    <td><a class='btn btn-success btn-sm' href="{{route('show_product', $product->id)}}" title="Détails du produit"> Détails </a></td></tr>

@endforeach
    </tbody>
</div>

@endsection
