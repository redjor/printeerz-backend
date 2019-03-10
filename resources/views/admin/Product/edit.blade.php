@extends('layouts/templateAdmin')

@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-8">
            <!-- Header -->
            <div class="header">
                <div class="header-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <!-- Pretitle -->
                            <h6 class="header-pretitle">
                                MODIFICATION
                            </h6>
                            <!-- Title -->
                            <h1 class="header-title">
                                Modifier un produit
                            </h1>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>
                </div>
            </div>
            {{-- Body --}}
            {!! Form::open(['action' => array('ProductController@update'), 'id' => $product->id, 'files' => true,
            'class' => 'mb-4']) !!}
            <div class="row">
                {{csrf_field()}}
                <div class="col-12">
                    <!-- First name -->
                    <div class="form-group">
                        <label>
                            Nom du produit
                        </label>
                        {!! Form::text('title', $product->title, ['class' => 'form-control' . $errors->first('title', '
                        is-invalid'), 'placeholder' => 'Nom du produit'])!!}
                        @if($errors->has('title'))<div class="invalid-feedback">Veuillez renseigner le nom du produit</div>@endif
                    </div>
                </div>
            </div>
            <hr class="mt-4 mb-5">
            <div class="row">
                <div class="col-12 col-md-6">
                    <!-- First name -->
                    <div class="form-group">
                        <!-- Label -->
                        <label>
                            Type de produit
                        </label>
                        <!-- Input -->
                        {!! Form::text('product_type', $product->product_type, ['class' => 'form-control'. $errors->first('product_type', '
                        is-invalid'),
                        'placeholder' => 'Type de produit'])
                        !!}
                        @if($errors->has('product_type'))<div class="invalid-feedback">Veuillez renseigner le nom du produit</div>@endif
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label>Genre du produit</label>
                        @if($product->gender == "male")
                        <select name="gender" id="gender" class="form-control" data-toggle="select">
                            <option value="male" selected>Homme</option>
                            <option value="female">Femme</option>
                            <option value="unisex">Unisex</option>
                            <option value="accessories">Accessoires</option>
                        </select>
                        @else
                        <select name="gender" id="gender" class="form-control" data-toggle="select">
                            <option value="male">Homme</option>
                            <option value="female" selected>Femme</option>
                            <option value="unisex">Unisex</option>
                            <option value="accessories">Accessoires</option>
                        </select>
                        @endif
                    </div>
                </div>
                <input type="hidden" class="form-control" name="actual_title" value="{{$product->title}}">
                <input type="hidden" class="form-control" name="product_id" value="{{$product->id}}">
            </div>
            <hr class="mt-4 mb-5">
            <div class="row">
                <div class="col-12">
                    <p class="h3">Fournisseur</p>
                    <p class="text-muted b-4">Renseigner les informations du fournisseur</p>
                </div>
                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label>
                            Nom
                        </label>
                        {!! Form::text('vendor_name', $product->vendor['name'], ['class' => 'form-control',
                        'placeholder' => 'Nom']) !!}
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label>
                            Référence
                        </label>
                        {!! Form::text('vendor_reference', $product->vendor['reference'], ['class' => 'form-control',
                        'placeholder' => 'Référence']) !!}
                    </div>
                </div>
            </div>
            <hr class="mt-4 mb-5">
            <div class="row">
                <div class="col-12">
                    <p class="h3">Image</p>
                    <p class="text-muted mb-4">Modifier l'image du produit en format 1:1</p>
                </div>
                <div class="col-12">
                    <!-- First name -->
                    <div class="form-group">
                        <div class="custom-file">
                            {!! Form::file('image', array('class' => 'custom-file-input', 'id' => 'logo_img')) !!}
                            <label class="custom-file-label" for="projectCoverUploads">Télécharger l'image du produit</label>
                        </div>
                    </div>
                </div>
            </div>
            <hr class="mt-4 mb-5">
            <div class="row">
                <div class="col-12">
                    <!-- First name -->
                    <div class="form-group">
                        <p class="h3"> Zones d'impression</p>
                        <p class="text-muted b-4">Sélectionner les zones d'impression qui seront disponibles pour ce
                            produit</p>
                        {!! Form::select('printzones_id[]', App\Printzones::pluck('name','_id'),
                        $product->printzones_id, ['class' => 'form-control', 'multiple', 'data-toggle' => 'select'])
                        !!}
                    </div>
                </div>
            </div>
            <hr class="mt-4 mb-5">
            <div class="row">
                <div class="col-12">
                    <div class="form-group">
                        <p class="h3">Description</p>
                        <p class="text-muted b-4">Ecriver une description rapide du produit (max: 750 caractères)</p>
                        <input id="textDescription" type="textarea" class="description" name="description" rows="3"
                            value="{{ $product->description }}">
                    </div>
                    <hr class="mt-4 mb-5">

                    <div class="row">
                        <div class="col-12">
                            <div class="buttons">
                                {!! Form::submit('Modifier le produit', ['class' => 'btn btn-primary', 'style' =>
                                'float: right'])
                                !!}
                                <a class='btn btn-secondary' style="float: left" href="{{route('index_product')}}">Annuler</a>
                            </div>
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
        @endsection