@extends('layouts.app')
@auth
@section('title','Dashboard - Corral X')

@section('content')
<main class="c-main">
    <div class="container-fluid">
        <div id="ui-view">
            <div class="fade-in">
                <!-- Header -->
                <div class="mb-4">
                    <h1 class="h3 mb-0">Dashboard</h1>
                    <p class="text-muted">Resumen general del marketplace Corral X</p>
                </div>

                <!-- Estadísticas principales -->
                <div class="row">
                    <!-- Usuarios -->
                    <div class="col-sm-6 col-lg-3">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <div class="text-value-lg">{{ number_format($stats['users']) }}</div>
                                <div>Usuarios Totales</div>
                                <div class="progress progress-white progress-xs my-2">
                                    <div class="progress-bar" role="progressbar" style="width: 100%"></div>
                                </div>
                                <small class="text-muted">Registrados en la plataforma</small>
                            </div>
                        </div>
                    </div>

                    <!-- Perfiles -->
                    <div class="col-sm-6 col-lg-3">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <div class="text-value-lg">{{ number_format($stats['profiles']) }}</div>
                                <div>Perfiles Completados</div>
                                <div class="progress progress-white progress-xs my-2">
                                    <div class="progress-bar" role="progressbar" style="width: 100%"></div>
                                </div>
                                <small class="text-muted">Perfiles de ganaderos</small>
                            </div>
                        </div>
                    </div>

                    <!-- Productos -->
                    <div class="col-sm-6 col-lg-3">
                        <div class="card text-white" style="background-color: #386A20;">
                            <div class="card-body">
                                <div class="text-value-lg">{{ number_format($stats['products']) }}</div>
                                <div>Productos Publicados</div>
                                <div class="progress progress-white progress-xs my-2">
                                    <div class="progress-bar" role="progressbar" style="width: {{ $stats['products'] > 0 ? ($stats['products_active'] / $stats['products'] * 100) : 0 }}%"></div>
                                </div>
                                <small class="text-muted">{{ $stats['products_active'] }} activos, {{ $stats['products_sold'] }} vendidos</small>
                            </div>
                        </div>
                    </div>

                    <!-- Haciendas -->
                    <div class="col-sm-6 col-lg-3">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <div class="text-value-lg">{{ number_format($stats['ranches']) }}</div>
                                <div>Haciendas Registradas</div>
                                <div class="progress progress-white progress-xs my-2">
                                    <div class="progress-bar" role="progressbar" style="width: 100%"></div>
                                </div>
                                <small class="text-muted">Fincas y hatos</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas secundarias -->
                <div class="row">
                    <!-- Pedidos -->
                    <div class="col-sm-6 col-lg-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="text-value-lg" style="color: #386A20;">{{ number_format($stats['orders']) }}</div>
                                <div class="text-uppercase text-muted small font-weight-bold">Pedidos Totales</div>
                                <div class="mt-2">
                                    <small class="text-muted">{{ $stats['orders_pending'] }} pendientes, {{ $stats['orders_completed'] }} completados</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Conversaciones -->
                    <div class="col-sm-6 col-lg-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="text-value-lg text-info">{{ number_format($stats['conversations']) }}</div>
                                <div class="text-uppercase text-muted small font-weight-bold">Conversaciones Activas</div>
                                <div class="mt-2">
                                    <small class="text-muted">Chats en curso</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reseñas -->
                    <div class="col-sm-6 col-lg-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="text-value-lg text-warning">{{ number_format($stats['reviews']) }}</div>
                                <div class="text-uppercase text-muted small font-weight-bold">Reseñas Totales</div>
                                <div class="mt-2">
                                    <small class="text-muted">Calificaciones de usuarios</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Favoritos -->
                    <div class="col-sm-6 col-lg-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="text-value-lg text-danger">{{ number_format($stats['favorites']) }}</div>
                                <div class="text-uppercase text-muted small font-weight-bold">Favoritos</div>
                                <div class="mt-2">
                                    <small class="text-muted">Productos guardados</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas adicionales -->
                <div class="row mt-3">
                    <!-- Reportes Pendientes -->
                    <div class="col-sm-6 col-lg-3">
                        <div class="card border-warning">
                            <div class="card-body">
                                <div class="text-value-lg text-warning">{{ number_format($stats['reports']) }}</div>
                                <div class="text-uppercase text-muted small font-weight-bold">Reportes Pendientes</div>
                                <div class="mt-2">
                                    <small class="text-muted">Requieren revisión</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Anuncios Activos -->
                    <div class="col-sm-6 col-lg-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="text-value-lg" style="color: #386A20;">{{ number_format($stats['advertisements']) }}</div>
                                <div class="text-uppercase text-muted small font-weight-bold">Anuncios Activos</div>
                                <div class="mt-2">
                                    <small class="text-muted">Publicidad en marketplace</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Roles -->
                    <div class="col-sm-6 col-lg-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="text-value-lg text-success">{{ number_format($stats['roles']) }}</div>
                                <div class="text-uppercase text-muted small font-weight-bold">Roles Configurados</div>
                                <div class="mt-2">
                                    <small class="text-muted">Sistema de permisos</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Acciones rápidas -->
                    <div class="col-sm-6 col-lg-3">
                        <div class="card" style="border-color: #386A20;">
                            <div class="card-body">
                                <h6 class="text-uppercase text-muted small font-weight-bold mb-3">Acciones Rápidas</h6>
                                <div class="d-flex flex-column gap-2">
                                    @can('haveaccess', 'users.index')
                                    <a href="{{ route('users.index') }}" class="btn btn-sm" style="background-color: #386A20; color: white; border: none;">
                                        <svg class="c-icon">
                                            <use xlink:href="{{asset('icons/sprites/free.svg#cil-people')}}"></use>
                                        </svg> Gestionar Usuarios
                                    </a>
                                    @endcan
                                    @can('haveaccess', 'roles.index')
                                    <a href="{{ route('roles.index') }}" class="btn btn-sm btn-outline-secondary">
                                        <svg class="c-icon">
                                            <use xlink:href="{{asset('icons/sprites/free.svg#cil-user')}}"></use>
                                        </svg> Gestionar Roles
                                    </a>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tablas de información reciente -->
                <div class="row">
                    <!-- Usuarios recientes -->
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <strong>Usuarios Recientes</strong>
                            </div>
                            <div class="card-body">
                                <table class="table table-responsive-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Email</th>
                                            <th>Registrado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recent_users as $user)
                                        <tr>
                                            <td>{{ $user->name }}</td>
                                            <td>{{ $user->email }}</td>
                                            <td>{{ $user->created_at->format('d/m/Y') }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">No hay usuarios recientes</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Productos recientes -->
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <strong>Productos Recientes</strong>
                            </div>
                            <div class="card-body">
                                <table class="table table-responsive-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Título</th>
                                            <th>Vendedor</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recent_products as $product)
                                        <tr>
                                            <td>{{ Str::limit($product->title, 30) }}</td>
                                            <td>{{ $product->ranch->profile->firstName ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge badge-{{ $product->status === 'active' ? 'success' : 'secondary' }}">
                                                    {{ ucfirst($product->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">No hay productos recientes</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
@endauth
