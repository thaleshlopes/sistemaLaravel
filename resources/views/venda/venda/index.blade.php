@extends('layouts.admin')
@section('conteudo')
<div class="row">
	<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
		<h3>Lista de Comandas <a href="venda/create"><button class="btn btn-success">Novo</button></a></h3>
		<!--@include('venda.venda.search') -->
	</div>
</div>

<div class="row">


@foreach ($vendas as $ent)
        <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box">
            <span class="info-box-icon bg-blue"><i class="fa fa-shopping-cart"></i></span>

            <div class="info-box-content">
              <span class="info-box-text">{{$ent->idvenda}} - {{$ent->nome}}</span>
              <span class="info-box-number">R$ {{$ent->total_venda}}</span>
              <span><a href="{{URL::action('VendaController@show',$ent->idvenda)}}" class="small-box-footer">Lançar/Fechar <i class="fa fa-arrow-circle-right"></i></a></span>
            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>

        @endforeach
    </div>
@stop