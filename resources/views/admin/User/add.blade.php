@extends('layouts.templateAdmin')

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
                                CREATION
                            </h6>
                            <!-- Title -->
                            <h1 class="header-title">
                                Créer un utilisateur
                            </h1>
                        </div>
                        <div class="col-auto">
                        </div>
                    </div>
                </div>
            </div>
            {{-- Body --}}
            @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            {!! Form::open(['action' => 'UserController@store', 'files' => true, 'class' => 'mb-4']) !!}
            {{ csrf_field() }}

            <div class="row">
                <div class="col-12">
                    <!-- First name -->
                    <div class="form-group {{ $errors->has('username') ? ' has-error' : '' }}">
                        <!-- Label -->
                        <label>
                            Nom d'utilisateur
                        </label>
                        <!-- Input -->
                        <input id="username" type="text" class="form-control" name="username" value="{{ old('username') }}"
                            required autofocus>
                        @if ($errors->has('username'))
                        <span class="help-block">
                            <strong>{{ $errors->first('username') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-6">
                    <div class="form-group{{ $errors->has('lastname') ? ' has-error' : '' }}">
                        <label for="lastname" class="label">Nom</label>
                        <input id="lastname" type="text" class="form-control" name="lastname" value="{{ old('lastname') }}"
                            required autofocus>

                        @if ($errors->has('lastname'))
                        <span class="help-block">
                            <strong>{{ $errors->first('lastname') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="form-group{{ $errors->has('firstname') ? ' has-error' : '' }}">
                        <label for="firstname" class="label">Prénom</label>
                        <input id="name" type="text" class="form-control" name="firstname" value="{{ old('firstname') }}"
                            required autofocus>

                        @if ($errors->has('firstname'))
                        <span class="help-block">
                            <strong>{{ $errors->first('firstname') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                        <label for="email" class="label">E-Mail</label>
                        <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}"
                            required>
                        @if ($errors->has('email'))
                        <span class="help-block">
                            <strong>{{ $errors->first('email') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <label for="phone">Téléphone</label>
                    {!! Form::text('phone', null, ['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-6">
                    <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                        <label for="password" class="label">Mot de passe</label>
                        <input id="password" type="password" class="form-control" name="password" required>
                        @if ($errors->has('password'))
                        <span class="help-block">
                            <strong>{{ $errors->first('password') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label for="password-confirm" class="label">Confirmation du mot de passe</label>
                        <input id="password-confirm" type="password" class="form-control" name="password_confirmation"
                            required>
                    </div>
                </div>
            </div>
            <hr class="mt-4 mb-5">
            <div class="row">
                <div class="col-12">
                    <p class="h3">Image de profil</p>
                    <p class="mb-4">Ajouter une image de profil</p>
                </div>
                <div class="col-12">
                    <!-- First name -->
                    <div class="form-group">
                        <div class="dropzone dropzone-single mb-3" data-toggle="dropzone" data-dropzone-url="http://"
                            id="logo_event_upload">
                            <div class="fallback">
                                <div class="custom-file">
                                    {!! Form::file('profile_img', array('class' => 'custom-file-input', 'id' =>
                                    'photo_profile')) !!}

                                    <label class="custom-file-label" for="projectCoverUploads">Choose file</label>
                                </div>
                            </div>
                            <div class="dz-preview dz-preview-single">
                                <div class="dz-preview-cover">
                                    <img class="dz-preview-img" src="..." alt="..." data-dz-thumbnail>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <hr class="mt-4 mb-5">
            <div class="row">
                <div class="col-12">
                    <p class="h3">Role</p>
                    <p class="mb-4">Sélectionner le role de l'utilisateur</p>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <select name="" id="" class="form-control" data-toggle="select">
                            <option value="1">Opérateur</option>
                            <option value="2">Technicien</option>
                            <option value="3">Administrateur</option>
                        </select>
                    </div>
                </div>
                <input type="hidden" class="form-control" name="is_active" value=true>
                <input type="hidden" class="form-control" name="is_deleted" value=false>
            </div>
            <hr class="mt-4 mb-5">
            <div class="row">
                <div class="col-12">
                    <div class="buttons">
                        <button type="submit" class="btn btn-primary" style="float:right;">
                            Créer l'utilisateur
                        </button>
                        <a class='btn btn-secondary' style="float: left;" href="/admin/User/index">Annuler</a>
                    </div>
                </div>
            </div>
            </form>
        </div>
    </div>
</div>
@endsection