<?php

namespace sistemaLaravel\Http\Controllers;

use Illuminate\Http\Request;
use sistemaLaravel\Entrada;
use sistemaLaravel\DetalheEntrada;
use Illuminate\Support\Facades\Redirect;
use sistemaLaravel\Http\Requests\EntradaFormRequest;
use DB;

use Carbon\Carbon;
use Response;
use Illuminate\Support\Collection;

class EntradaController extends Controller
{
    public function __construct(){
    	$this->middleware('auth');
    }

    public function index(Request $request){

    	if($request){
    		$query=trim($request->get('searchText'));
    		$entradas = DB::table('entrada as e')
    		->join('pessoa as p', 'e.idfornecedor', '=', 'p.idpessoas')
    		->join('detalhe_entrada as de', 'e.identrada', '=', 'de.identrada')
    		->select('e.identrada', 'e.data_hora', 'p.nome', 'e.tipo_comprovante', 'e.serie_comprovante', 'e.num_comprovante', 'e.taxa', 'e.estado', DB::raw('sum(de.quantidade*preco_compra)as total'))
            ->where('estado', '=', 'A')
    		->where('e.num_comprovante', 'LIKE', '%'.$query.'%')
    		
    		->orderBy('e.identrada', 'desc')
    		->groupBy('e.identrada', 'e.data_hora', 'p.nome', 'e.tipo_comprovante', 'e.serie_comprovante', 'e.num_comprovante', 'e.taxa', 'e.estado')
    		->paginate(7);

    		return view('compra.entrada.index', [
    			"entradas"=>$entradas, "searchText"=>$query
    			]);
    	}
    }

    public function create(){
    	$pessoas=DB::table('pessoa')
    	->where('tipo_pessoa', '=', 'Fornecedor')->get();
    	$produtos=DB::table('produto as pro')
    	->select(DB::raw('CONCAT(pro.nome, "   -   ", pro.codigo) As produto'), 'pro.idproduto')
    	->where('pro.estado', '=', 'Ativo')
    	->get();
    	return view("compra.entrada.create", ["pessoas"=>$pessoas, "produtos"=>$produtos]);
    }
 
    public function store(EntradaFormRequest $request){

    	try{
    		DB::beginTransaction();
	    	$entrada = new Entrada;
	    	$entrada->idfornecedor=$request->get('idfornecedor');
	    	$entrada->tipo_comprovante=$request->get('tipo_comprovante');
	    	$entrada->serie_comprovante=$request->get('serie_comprovante');
	    	$entrada->num_comprovante=$request->get('num_comprovante');
	    	$mytime = Carbon::now('America/Sao_Paulo');
	    	$entrada->data_hora=$mytime->toDateTimeString();
	    	$entrada->taxa='0';
	    	$entrada->estado='A';
	    	$entrada->save();
    	
	    	$idproduto=$request->get('idproduto');
	    	$quantidade=$request->get('quantidade');
	    	$preco_compra=$request->get('preco_compra');
	    	$preco_venda=$request->get('preco_venda');

	    	$cont = 0;
	    	while($cont < count($idproduto)){
	    		$detalhe = new DetalheEntrada();
	    		$detalhe->identrada=$entrada->identrada;
	    		$detalhe->idproduto=$idproduto[$cont];
	    		$detalhe->quantidade=$quantidade[$cont];
	    		$detalhe->preco_compra=$preco_compra[$cont];
	    		$detalhe->preco_venda=$preco_venda[$cont];
	    		$detalhe->save();
	    		$cont=$cont+1;
	    	}

	    	DB::commit();

    	

    		

    	}catch(\Exception $e){
    		DB::rollback();
    	}

    	return Redirect::to('compra/entrada');

    }

    public function show($id){

    	$entrada=DB::table('entrada as e')
    		->join('pessoa as p', 'e.idfornecedor', '=', 'p.idpessoas')
    		->join('detalhe_entrada as de', 'e.identrada', '=', 'de.identrada')
    		->select('e.identrada', 'e.data_hora', 'p.nome', 'e.tipo_comprovante', 'e.serie_comprovante', 'e.num_comprovante', 'e.taxa', 'e.estado', DB::raw('sum(de.quantidade*preco_compra)as total'))
    		->where('e.identrada', '=', $id)
            ->groupBy('e.identrada', 'e.data_hora', 'p.nome', 'e.tipo_comprovante', 'e.serie_comprovante', 'e.num_comprovante', 'e.taxa', 'e.estado')
            ->first();


    		$detalhes=DB::table('detalhe_entrada as d')
    		->join('produto as p', 'd.idproduto', '=', 'p.idproduto' )
    		->select('p.nome as produto', 'd.quantidade', 'd.preco_compra', 'd.preco_venda')
    		->where('d.identrada', '=', $id)
    		->get();


    	return view("compra.entrada.show", 
          		["entrada"=>$entrada, "detalhes"=>$detalhes]);
    }

    

    public function destroy($id){
    	$entrada=Entrada::findOrFail($id);
    	$entrada->estado='C';
    	$entrada->update();
    	return Redirect::to('compra/entrada');
    }
}
