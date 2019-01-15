<?php

namespace App\Http\Controllers;

use DB;
use App\Product;
use App\Taille;
use App\Zone;
use App\Couleur;
use App\ImageZone;
use App\Gabarit;
use App\ProductVariants;

use Illuminate\Http\Request;
use App\Http\Middleware\isAdmin;
use App\Http\Middleware\isActivate;

class ProductvariantsController extends Controller
{
    public function __construct(){
        //$this->middleware(isActivate::class);
        //$this->middleware(isAdmin::class);
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $couleurs = Couleur::all();
        return view('admin/Couleur.index', ['couleurs' => $couleurs, 'tailles' => $tailles]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id)
    {
        $couleurs = Couleur::all();
        $tailles = Taille::all();
        $product = Product::find($id);
        $select_couleurs = [];
        foreach($couleurs as $couleur){
            $select_couleurs[$couleur->id] = $couleur->nom;
        }
        $select_tailles = [];
        foreach($tailles as $taille){
            $select_tailles[$taille->id] = $taille->nom;
        }
        return view('admin/Productvariants.add', ['tailles' => $tailles,'select_couleurs' => $select_couleurs, 'select_tailles' => $select_tailles, 'product_id' => $id]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'couleur_id' => 'required|integer|max:255'
        ]);

        $taille_checkboxes = $request->get('taille_id');
        
        foreach($taille_checkboxes as $taille_checkbox){
            $productVariant = new ProductVariants; 

            $productVariant->couleur_id = $request->couleur_id;
            $productVariant->product_id = $request->product_id;
            $productVariant->taille_id = $taille_checkbox;
            $productVariant->couleur_nom = $productVariant->couleur->nom;
            $productVariant->taille_nom = $productVariant->taille->nom;
            $productVariant->product_nom = $productVariant->product->nom;
            $productVariant->qty = $request->qty;

            $productVariant->zone1 = $request->zone1;
            $productVariant->zone2 = $request->zone2;
            $productVariant->zone3 = $request->zone3;
            $productVariant->zone4 = $request->zone4;

            $productVariant->image1 = $request->image1;
            $productVariant->image2 = $request->image2;
            $productVariant->image3 = $request->image3;
            $productVariant->image4 = $request->image4;

            $productVariant->gabarit1 = $request->gabarit1;
            $productVariant->gabarit2 = $request->gabarit2;
            $productVariant->gabarit3 = $request->gabarit3;
            $productVariant->gabarit4 = $request->gabarit4;

            $productVariant->save();
        }

        if($request->file('image1')){
            foreach($request->file('image1') as $media1){
                    $image1 = time().'1.'.$media1->getClientOriginalExtension();
                    $media1->move(public_path('uploads'), $image1);
                    $productVariant->image1 = $image1;
            }
        }

        if($request->file('image2')){
            foreach($request->file('image2') as $media2){
                    $image2 = time().'2.'.$media2->getClientOriginalExtension();
                    $media2->move(public_path('uploads'), $image2);
                    $productVariant->image2 = $image2;
            }
        }

        if($request->file('image3')){
            foreach($request->file('image3') as $media3){
                if(!empty($media3)){
                    $image3 = time().'3.'.request()->image3->getClientOriginalExtension();
                    request()->image3->move(public_path('uploads'), $image3);
                    $productVariant->image3 = $image3;
                }
            }
        }

        if($request->file('image4')){
            foreach($request->file('image4') as $media){
                if(!empty($media4)){
                    $image4 = time().'4.'.request()->image4->getClientOriginalExtension();
                    request()->image4->move(public_path('uploads'), $image4);
                    $productVariant->image4 = $image4;
                }
            }
        }

            // if ($request->hasFile('image2')){
            //     $image2 = time().'2.'.request()->image2->getClientOriginalExtension();
            //     request()->image2->move(public_path('uploads'), $image2);
            //     $productVariant->image2 = $image2;
            // }

            // if ($request->hasFile('image3')){
            //     $image3 = time().'3.'.request()->image3->getClientOriginalExtension();
            //     request()->image3->move(public_path('uploads'), $image3);
            //     $productVariant->image3 = $image3;
            // }

            // if ($request->hasFile('image4')){
            //     $image4 = time().'4.'.request()->image4->getClientOriginalExtension();
            //     request()->image4->move(public_path('uploads'), $image4);
            //     $productVariant->image4 = $image4;
            // }

            // if ($request->hasFile('image5')){
            //     $image5 = time().'5.'.request()->image5->getClientOriginalExtension();
            //     request()->image5->move(public_path('uploads'), $image5);
            //     $productVariant->image5 = $image5;
            // }

            $productVariant->save();
       
        
        $product = Product::find($productVariant->product_id);
        $productVariants = ProductVariants::all();
        return view('admin/Product.show',['productVariants' => $productVariants, 'product' => $product, 'id' => $product->id])->with('status', 'La variante a été correctement ajoutée.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $productVariant = ProductVariants::find($id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $productVariant = ProductVariants::find($id);
        $couleurs = Couleur::all();
        $select_couleurs = [];
        foreach($couleurs as $couleur){
            $select_couleurs[$couleur->id] = $couleur->nom;
        }
        return view('admin/ProductVariants.edit', ['couleurs' => $couleurs, 'select_couleurs' => $select_couleurs]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        if (request('actual_color') == request('color')){
            $validatedData = $request->validate([
                'color' => 'required|string|max:255',
                'zone1' => 'required|string|max:255',
                'zone2' => 'required|string|max:255',
                'zone3' => 'required|string|max:255',
                'zone4' => 'required|string|max:255',
                'zone5' => 'required|string|max:255',
                'image1' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'image2' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'image3' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'image4' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'image5' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048'
            ]);
            $id = $request->id;
            $productVariant = ProductVariants::find($id);
            
            $productVariant->color = $request->color;
            $productVariant->product_id = $request->product_id;
    
            $productVariant->zone1 = $request->zone1;
            $productVariant->zone2 = $request->zone2;
            $productVariant->zone3 = $request->zone3;
            $productVariant->zone4 = $request->zone4;
            $productVariant->zone5 = $request->zone5;
            
            $productVariant->image1 = $request->image1;
            $productVariant->image2 = $request->image2;
            $productVariant->image3 = $request->image3;
            $productVariant->image4 = $request->image4;
            $productVariant->image5 = $request->image5;
    
            $productVariant->save();
            }        
            else{
                $validatedData = $request->validate([
                    'color' => 'required|string|max:255',
                    'zone1' => 'required|string|max:255',
                    'zone2' => 'required|string|max:255',
                    'zone3' => 'required|string|max:255',
                    'zone4' => 'required|string|max:255',
                    'zone5' => 'required|string|max:255',
                    'image1' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                    'image2' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                    'image3' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                    'image4' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                    'image5' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048'
                ]);
                $id = $request->id;
                $productVariant = ProductVariants::find($id);
                
                $productVariant->color = $request->color;
                $productVariant->product_id = $request->product_id;
        
                $productVariant->zone1 = $request->zone1;
                $productVariant->zone2 = $request->zone2;
                $productVariant->zone3 = $request->zone3;
                $productVariant->zone4 = $request->zone4;
                $productVariant->zone5 = $request->zone5;
                
                $productVariant->image1 = $request->image1;
                $productVariant->image2 = $request->image2;
                $productVariant->image3 = $request->image3;
                $productVariant->image4 = $request->image4;
                $productVariant->image5 = $request->image5;
        
                $productVariant->save();
        }
            return redirect('admin/Product/index')->with('status', 'Le produit a été correctement modifié.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $productVariant = ProductVariants::find($id);
        if($productVariant) {
            $productVariant->delete();
            $product = Product::find($productVariant->product_id);
            $productVariants = ProductVariants::all();
            return view('admin/Product.show',['productVariants' => $productVariants, 'product' => $product, 'id' => $product->id])->with('status', 'La variante a été correctement supprimée.');
        }
        else {
            return redirect('admin/Product/index');
        }
    }
}
