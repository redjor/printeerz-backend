<?php

namespace App\Http\Controllers;

use DB;
use App\Events_customs;
use App\Events_component;
use App\Product;
use App\Events_products;
use App\Templates;
use App\Template_components;
use App\Printzones;

use Illuminate\Http\Request;

use Image;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

class EventsCustomsController extends Controller
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
        $events_customs = Events_customs::all();
        return view('admin/Events_customs.index', ['events_customs' => $events_customs]);
    }

    /**
     * Show the form for creating a new resource.
     * 
     * @param  int  $id
     * @return \Illuminate\Http\Response
     *
     */
    public function create($id)
    {
        $events_customs = Events_customs::all();
        $events_product = Events_products::find($id);
        $product = Product::find($events_product->product_id);
        $printzones = Printzones::all();
        $templates = Templates::all();
        $select_templates = [];
        foreach($templates as $template) {
            $select_templates[$template->id] = $template->title;
        }
        $select_printzones = [];
        if($product->printzones_id != null) {
            foreach($printzones as $printzone) {
                foreach($product->printzones_id as $printzone_id) {
                    if($printzone_id == $printzone->id) {
                        $select_printzones[$printzone->id] = $printzone->zone;
                    }
                }
            }
        }
        return view('admin/EventsCustoms.add', ['select_printzones' => $select_printzones, 'templates' => $templates, 'select_templates' => $select_templates, 'product' => $product, 'events_customs' => $events_customs, 'events_product' => $events_product]);
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
            'title' => 'required|string|max:255'
        ]);
        $disk = Storage::disk('s3');
        $s3 = 'https://s3.eu-west-3.amazonaws.com/printeerz-dev';
        $events_custom = new Events_customs;
        $events_custom->title = $request->title;
        $events_custom->event_id = $request->get('event_id');
        $events_custom->events_product_id = $request->get('events_product_id');
        $events_custom->events_product_variants_ids = $request->get('event_product_variants_ids');
        $events_custom->template = array(
            $request->template_id,
            $request->printzone_id,
        );
        $events_custom->is_active = $request->is_active;
        $events_custom->is_deleted = $request->is_deleted;
        $events_custom->save();
        if ($request->hasFile('custom_img')){
            // Get file
            $file = $request->file('custom_img');
            // Create name
            $name = time() . $file->getClientOriginalName();
            // Define the path
            $filePath = '/events/'. $events_custom->event_id . '/'. $events_custom->id . '/'. $name;
            // Resize img
            $img = Image::make(file_get_contents($file))->widen(300)->save($name);
            // Upload the file
            $disk->put($filePath, $img, 'public');
            // Delete public copy
            unlink(public_path() . '/' . $name);
            // Put in database
            $events_custom->image = $filePath;
        }
        $events_custom->save();
        // add events_component for every component in the template selected for the events_custom
        $templates = Templates::all();
        foreach($templates as $template){
            if($template->id == $request->template_id){
                $template_selected = $template;
            }
        }
        $template_components = Template_components::all();
        foreach($template_components as $template_component){
            foreach($template_selected->components_ids as $component_id){
                if($template_component->id == $component_id['id']){
                    $events_component = new Events_component;
                    $events_component->title = $template_component->title;
                    $events_component->events_custom_id =  $events_custom->id;
                    $events_component->template_component_id =  $template_component->id;
                    $events_component->type = $template_component->comp_type;
                    $events_component->event_id = $request->event_id;
                    $events_component->events_product_id = $request->events_product_id;
                    $events_component->template_id = $request->template_id;
                    $events_component->printzone_id = $request->printzone_id;
                    $events_component->width = $template_component->size['width'];
                    $events_component->height = $template_component->size['height'];
                    $events_component->origin_x =  $template_component->origin['x'];
                    $events_component->origin_y = $template_component->origin['y'];
                    $events_component->input_min = $template_component->characters['min'];
                    $events_component->input_max = $template_component->characters['max'];
                    $events_component->highlight = $template_component->highlight;
                    $events_component->save();
                }
            }
        }

        return redirect('admin/EventsCustoms/edit/'.$events_custom->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $events_custom = Events_customs::find($id);
        $events_customs = Events_customs::all();
        $templates = Templates::all();
        $template_components = Template_components::all();
        $events_components = Events_component::all();
        $disk = Storage::disk('s3');
        $s3 = 'https://s3.eu-west-3.amazonaws.com/printeerz-dev';
        $select_templates = [];
        foreach($templates as $template) {
            $select_templates[$template->id] = $template->title;
        }
        if($events_custom->events_product_id != null){
            $events_product = Events_products::find($events_custom->events_product_id);
        }
        $product = Product::find($events_product->product_id);
        $printzones = Printzones::all();
        $font_transform = [
            'none'=>'Aucune',
            'uppercase'=>'Tout en Majuscules',
            'capitalize'=>'Première lettre en Majuscule',
            'lowercase'=>'Tout en minuscule',
            'full-width'=>'Pleine largeur'
        ];
        $font_weight = [
            '100'=>'Thin (100)',
            '200'=>'Extra Light (200)',
            '300'=>'Light (300)',
            '400'=>'Normal (400)',
            '500'=>'Medium (500)',
            '600'=>'Semi Bold (600)',
            '700'=>'Bold (700)',
            '800'=>'Extra Bold (800)',
            '900'=>'Black (900)'
        ];
        $select_printzones = [];
        if($product->printzones_id != null){
            foreach($printzones as $printzone){
                foreach($product->printzones_id as $printzone_id){
                    if($printzone_id == $printzone->id) {
                        $select_printzones[$printzone->id] = $printzone->zone;
                    }
                }
            }
        }
        return view('admin/EventsCustoms.show', ['events_components'=>$events_components,'disk'=>$disk,'s3'=>$s3,'font_weight'=>$font_weight,'font_transform'=>$font_transform,'events_customs' => $events_customs,'template_components' => $template_components, 'templates' => $templates, 'events_custom' => $events_custom, 'select_printzones' => $select_printzones, 'select_templates' => $select_templates, 'events_product' => $events_product]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $events_custom = Events_customs::find($id);
        $templates = Templates::all();
        $template_components = Template_components::all();
        $disk = Storage::disk('s3');
        $s3 = 'https://s3.eu-west-3.amazonaws.com/printeerz-dev';
        $select_templates = [];
        foreach ($templates as $template) {
            $select_templates[$template->id] = $template->title;
        }
        if ($events_custom->events_product_id != null) {
            $events_product = Events_products::find($events_custom->events_product_id);
        }
        $product = Product::find($events_product->product_id);
        $printzones = Printzones::all();
        $events_components = Events_component::all();
        $select_printzones = [];
        if ($product->printzones_id != null){
            foreach ($printzones as $printzone) {
                foreach ($product->printzones_id as $printzone_id) {
                    if ($printzone_id == $printzone->id) {
                        $select_printzones[$printzone->id] = $printzone->zone;
                    }
                }
            }
        }
        return view('admin/EventsCustoms.edit', ['events_components'=>$events_components,'disk'=>$disk,'s3'=>$s3,'template_components' => $template_components, 'templates' => $templates, 'events_custom' => $events_custom, 'select_printzones' => $select_printzones, 'select_templates' => $select_templates, 'events_product' => $events_product]);
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
        if (request('actual_title') == request('title')){
            $validatedData = $request->validate([
                'title' => 'string|max:255'
            ]);
            $disk = Storage::disk('s3'); //ajouter des $i dans les input
            $events_custom_id = $request->events_custom_id;
            $events_custom = Events_customs::find($events_custom_id);
            $events_custom->components = array();
<<<<<<< HEAD

            for($i=1;$i<6;$i++){
                $template_component_id= $request->{'template_component_id'.$i};
                if($request->{'template_component_id'.$i}){
                    $array_colors = array();
                    foreach($request->{'colorsList'.$template_component_id} as $colcode){
                        foreach($request->{'hexaList'.$template_component_id} as $hexa){
                            $col = explode(",", $colcode);
                            $hex = explode(",", $hexa);
=======
            $count_component = $request->countJS;
            for($i=1;$i<=$count_component;$i++){
                $template_component_id = $request->{'template_component_id'.$i};
                if($template_component_id != null){
                    if($request->{'comp_type_'.$template_component_id} == 'input') {
                        $array_colors = array();
                        $colors = array();
                        foreach($request->{'colorsList'.$template_component_id} as $colcode){
                            foreach($request->{'hexaList'.$template_component_id} as $hexa){
                                $col = explode(",", $colcode);
                                $hex = explode(",", $hexa);
                            }
>>>>>>> develop
                        }
                        for($j=1;$j<count($col);$j++){
                            $array = array(
                                'title' => $col[$j],
                                'code_hexa' => $hex[$j]
                            );
                            array_push($array_colors, $array);
                        }
                        
                        $array_fonts = array();
                        $fonts = array();
                        foreach ($request->{'fontsList'.$template_component_id} as $font_title) {
                            if ($request->{'font_urlList'.$template_component_id}) {
                                foreach ($request->{'font_urlList'.$template_component_id} as $font_url) {
                                    $font = explode(",", $font_title);
                                    $urls = explode(",", $font_url);
                                    array_push($fonts, $urls);
                                }
                            }
                        }
                        $array_fonts_weight = array();
                        $fonts_weight = array();
                        foreach ($request->{'fontsWeightList'.$template_component_id} as $font_weight) {
                            if ($request->{'fontsTransformList'.$template_component_id}) {
                                foreach ($request->{'fontsTransformList'.$template_component_id} as $font_transform) {
                                    $fonts_weight_exploded = explode(",", $font_weight);
                                    $fonts_transform_exploded = explode(",", $font_transform);
                                    array_push($fonts_weight, $fonts_transform_exploded);
                                }
                            }
                        }
                        foreach($request->{'fontsFileNameList'.$template_component_id} as $font_file_name){
                            $fonts_file_name_exploded = explode(",", $font_file_name);
                        }
                        for($k=1; $k<count($font); $k++){
                            $array_ft = array(
                                'title' => $font[$k],
                                'font_file_name' => $fonts_file_name_exploded[$k],
                                'font_weight' => $fonts_weight_exploded[$k],
                                'font_transform' => $fonts_transform_exploded[$k],
                                'font_url' => $urls[$k]
                            );
                            array_push($array_fonts, $array_ft);
                        }
                        $component_input = array(
                            'events_component_id' => $request->{'template_component_id'.$i},
                            'component_type' => $request->{'comp_type_'.$template_component_id},
                            'title' => $request->{'option_title'.$i},
                            // 'position' => $request->{'option_position'.$i},
                            'settings' => array(
                                'input_min' => $request->{'min'.$i},
                                'input_max' => $request->{'max'.$i},
                                'font_first_letter' => $request->{'font_first_letter'.$i},
                                'fonts' => $array_fonts,
                                'font_colors' => $array_colors,
                                'position' => array(
                                    'width' => $request->{'width'.$i},
                                    'height' => $request->{'height'.$i},
                                    'origin_x' => $request->{'origin_x'.$i},
                                    'origin_y' => $request->{'origin_y'.$i}
                                ),
                            ),
                        );
                        $array = $events_custom->components;
                        array_push($array, $component_input);
                        $events_custom->components = $array;
                    }
<<<<<<< HEAD
                    $array_fonts = array();
                    foreach($request->{'fontsList'.$template_component_id} as $font_title){
                        foreach($request->{'font_urlList'.$template_component_id} as $font_url){
                            $font = explode(",", $font_title);
                            $url = explode(",", $font_url);
=======
                    if($request->{'comp_type_'.$template_component_id} == 'image'){
                        if($request->hasFile('comp_image'.$i)){
                            $events_custom_event_id = $request->events_custom_event_id;
                            $image_file = $request->file('comp_image'.$i);
                            $option_title = $request->{'option_title'.$i};
                            $image_name = time().$image_file->getClientOriginalName();
                            $newFilePath = '/events/'.$events_custom_event_id.'/images/'.$option_title.'/'.$image_name;
                            $img_resized = Image::make(file_get_contents($image_file))->widen(300)->save($image_name);
                            $disk->put($newFilePath, $img_resized, 'public');
                            $image_file = $newFilePath;
                            $component = array(
                                'events_component_id' => $request->{'template_component_id'.$i},
                                'component_type' => $request->{'comp_type_'.$template_component_id},
                                'title' => $request->{'option_title'.$i},
                                // 'position' => $request->{'option_position'.$i},
                                'settings' => array(
                                    'image_name' => $image_name,
                                    'image_url' => $newFilePath, // là j'ai enlevé $newFilePath
                                    'position' => array(
                                        'width' => $request->{'width'.$i},
                                        'height' => $request->{'height'.$i},
                                        'origin_x' => $request->{'origin_x'.$i},
                                        'origin_y' => $request->{'origin_y'.$i}
                                    ),
                                ),
                            );
>>>>>>> develop
                        }
                        else {
                            $component = array(
                                'events_component_id' => $request->{'template_component_id'.$i},
                                'component_type' => $request->{'comp_type_'.$template_component_id},
                                'title' => $request->{'option_title'.$i},
                                // 'position' => $request->{'option_position'.$i},
                                'settings' => array(
                                    'position' => array(
                                        'width' => $request->{'width'.$i},
                                        'height' => $request->{'height'.$i},
                                        'origin_x' => $request->{'origin_x'.$i},
                                        'origin_y' => $request->{'origin_y'.$i}
                                    ),
                                ),
                            );
                        }
                        $array = $events_custom->components;
                        array_push($array, $component);
                        $events_custom->components = $array;
                    }
                }
            }
            
            $events_custom->description = $request->description;
            $events_custom->save();
            return redirect('admin/EventsProducts/show/'.$events_custom->events_product_id);
        }
        else {
            $validatedData = $request->validate([
                'title' => 'string|max:255'
            ]);
            $disk = Storage::disk('s3');
            $events_custom_id = $request->events_custom_id;
            $events_custom = Events_customs::find($events_custom_id);
            $events_custom->components = array();
<<<<<<< HEAD

            for($i=1;$i<6;$i++){
                $template_component_id= $request->{'template_component_id'.$i};
                if($request->{'template_component_id'.$i}){
                    $array_colors = array();
                    foreach($request->{'colorsList'.$template_component_id} as $colcode){
                        foreach($request->{'hexaList'.$template_component_id} as $hexa){
                            $col = explode(",", $colcode);
                            $hex = explode(",", $hexa);
=======
            $events_custom->title = $request->option_title;
            $count_component = $request->countJS;
            for($i=1;$i<=$count_component;$i++){
                $template_component_id = $request->{'template_component_id'.$i};
                if($template_component_id != null){
                    if($request->{'comp_type_'.$template_component_id} == 'input') {
                        $array_colors = array();
                        $colors = array();
                        foreach($request->{'colorsList'.$template_component_id} as $colcode){
                            foreach($request->{'hexaList'.$template_component_id} as $hexa){
                                $col = explode(",", $colcode);
                                $hex = explode(",", $hexa);
                            }
>>>>>>> develop
                        }
                        for($j=1;$j<count($col);$j++){
                            $array = array(
                                'title' => $col[$j],
                                'code_hexa' => $hex[$j]
                            );
                            array_push($array_colors, $array);
                        }
                        
                        $array_fonts = array();
                        $fonts = array();
                        foreach ($request->{'fontsList'.$template_component_id} as $font_title) {
                            if ($request->{'font_urlList'.$template_component_id}) {
                                foreach ($request->{'font_urlList'.$template_component_id} as $font_url) {
                                    $font = explode(",", $font_title);
                                    $urls = explode(",", $font_url);
                                    array_push($fonts, $urls);
                                }
                            }
                        }
                        $array_fonts_weight = array();
                        $fonts_weight = array();
                        foreach ($request->{'fontsWeightList'.$template_component_id} as $font_weight) {
                            if ($request->{'fontsTransformList'.$template_component_id}) {
                                foreach ($request->{'fontsTransformList'.$template_component_id} as $font_transform) {
                                    foreach($request->{'fontsFileNameList'.$template_component_id} as $font_file_name){
                                        $fonts_weight_exploded = explode(",", $font_weight);
                                        $fonts_transform_exploded = explode(",", $font_transform);
                                        $fonts_file_name_exploded = explode(",", $font_file_name);
                                        array_push($fonts_weight, $fonts_transform_exploded);
                                    }
                                }
                            }
                        }
                        for($k=1; $k<count($font); $k++){
                            $array_ft = array(
                                'title' => $font[$k],
                                'font_file_name' => $fonts_file_name_exploded[$k],
                                'font_weight' => $fonts_weight_exploded[$k],
                                'font_transform' => $fonts_transform_exploded[$k],
                                'font_url' => $urls[$k]
                            );
                            array_push($array_fonts, $array_ft);
                        }
                        $component_input = array(
                            'events_component_id' => $request->{'template_component_id'.$i},
                            'component_type' => $request->{'comp_type_'.$template_component_id},
                            'title' => $request->{'option_title'.$i},
                            // 'position' => $request->{'option_position'.$i},
                            'settings' => array(
                                'input_min' => $request->{'min'.$i},
                                'input_max' => $request->{'max'.$i},
                                'font_first_letter' => $request->{'font_first_letter'.$i},
                                'fonts' => $array_fonts,
                                'font_colors' => $array_colors,
                                'position' => array(
                                    'width' => $request->{'width'.$i},
                                    'height' => $request->{'height'.$i},
                                    'origin_x' => $request->{'origin_x'.$i},
                                    'origin_y' => $request->{'origin_y'.$i}
                                ),
                            ),
                        );
                        $array = $events_custom->components;
                        array_push($array, $component_input);
                        $events_custom->components = $array;
                    }
<<<<<<< HEAD
                    $array_fonts = array();
                    foreach($request->{'fontsList'.$template_component_id} as $font_title){
                        foreach($request->{'font_urlList'.$template_component_id} as $font_url){
                            $font = explode(",", $font_title);
                            $url = explode(",", $font_url);
                        }
                    }
                    for($k=0;$k<count($font);$k++){
                        $array_ft = array(
                            'title' => $font[$k],
                            'font_url' => $url[$k]
                        );
                        array_push($array_fonts, $array_ft);
                    }
                    $component = array(
                        'template_component_id' => $request->{'template_component_id'.$i},
                        'title' => $request->{'option_title'.$i},
                        'position' => $request->{'option_position'.$i},
                        'settings' => array(
                            'input_min' => $request->{'min'.$i},
                            'input_max' => $request->{'max'.$i},
                            'font_first_letter' => $request->{'font_first_letter'.$i},
                            'font_transform' => $request->{'font_transform'.$i},
                            'font_weight' => $request->{'font_weight'.$i},
                            'fonts' => $array_fonts,
                            'font_colors' => $array_colors,
                            'position' => array(
                                'width' => $request->{'width'.$i},
                                'height' => $request->{'height'.$i},
                                'origin_x' => $request->{'origin_x'.$i},
                                'origin_y' => $request->{'origin_y'.$i}
                            ),
                        ),
                    );
                    $array = $events_custom->components;
                    array_push($array, $component);
                    $events_custom->components = $array;
                }
            }
            $events_custom->save();
            
            if ($request->hasFile('thumb')){
                $photo1 = time().'.'.request()->thumb->getClientOriginalExtension();
                request()->thumb->move(public_path('uploads'), $photo1);
                $events_custom->thumb = $photo1;
            }
            if ($request->hasFile('image')){
                $thumb_name = time().'.1'.request()->image->getClientOriginalExtension();
                request()->image->move(public_path('uploads'), $thumb_name);
                $events_custom->image = $photo;
            }
            $events_custom->comments = $request->comments;
            $events_custom->is_active = $request->is_active;
            $events_custom->is_deleted = $request->is_deleted;
=======
                    if($request->{'comp_type_'.$template_component_id} == 'image'){
                        if($request->hasFile('comp_image'.$i)){
                            $events_custom_event_id = $request->events_custom_event_id;
                            $image_file = $request->file('comp_image'.$i);
                            $option_title = $request->{'option_title'.$i};
                            $image_name = time().$image_file->getClientOriginalName();
                            $newFilePath = '/events/'.$events_custom_event_id.'/images/'.$option_title.'/'.$image_name;
                            $img_resized = Image::make(file_get_contents($image_file))->widen(300)->save($image_name);
                            $disk->put($newFilePath, $img_resized, 'public');
                            $image_file = $newFilePath;
                            $component = array(
                                'events_component_id' => $request->{'template_component_id'.$i},
                                'component_type' => $request->{'comp_type_'.$template_component_id},
                                'title' => $request->{'option_title'.$i},
                                // 'position' => $request->{'option_position'.$i},
                                'settings' => array(
                                    'image_name' => $image_name,
                                    'image_url' => $newFilePath, // là j'ai enlevé $newFilePath
                                    'position' => array(
                                        'width' => $request->{'width'.$i},
                                        'height' => $request->{'height'.$i},
                                        'origin_x' => $request->{'origin_x'.$i},
                                        'origin_y' => $request->{'origin_y'.$i}
                                    ),
                                ),
                            );
                        }
                        else {
                            $component = array(
                                'events_component_id' => $request->{'template_component_id'.$i},
                                'component_type' => $request->{'comp_type_'.$template_component_id},
                                'title' => $request->{'option_title'.$i},
                                // 'position' => $request->{'option_position'.$i},
                                'settings' => array(
                                    'position' => array(
                                        'width' => $request->{'width'.$i},
                                        'height' => $request->{'height'.$i},
                                        'origin_x' => $request->{'origin_x'.$i},
                                        'origin_y' => $request->{'origin_y'.$i}
                                    ),
                                ),
                            );
                        }
                        $array = $events_custom->components;
                        array_push($array, $component);
                        $events_custom->components = $array;
                    }
                }
            }
            
            $events_custom->description = $request->description;
>>>>>>> develop
            $events_custom->save();
            return redirect('admin/EventsProducts/show/'.$events_custom->events_product_id);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $events_custom = Events_customs::find($id);
        $disk = Storage::disk('s3'); 
        foreach($events_custom->components as $component){
            if(isset($component['image_url'])){
                $disk->delete($component['image_url']);
            }
        }
        $events_custom->delete();
        $notification = array(
            'status' => 'La personnalisation a été correctement supprimée.',
            'alert-type' => 'success'
        );
        return redirect('admin/EventsProducts/show/'.$events_custom->events_product_id)->with($notification);
    }

    public function desactivate($id)
    {
        $events_custom = Events_customs::find($id);
        $events_custom->is_active = false;
        $events_custom->update();
        return redirect('admin/EventsProducts/show/'.$events_custom->events_product_id);
    }

    public function delete($id)
    {
        $events_custom = Events_customs::find($id);
        $events_custom->is_deleted = true;
        $events_custom->update();
        return redirect('admin/EventsProducts/show/'.$events_custom->events_product_id);
    }

    public function activate($id)
    {
        $events_custom = Events_customs::find($id);
        $events_custom->is_active = true;
        $events_custom->update();
        return redirect('admin/EventsProducts/show/'.$events_custom->events_product_id);
    }

    /**
     * Upload a new file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function uploadFile(Request $request)
    {
        if($request) {
            $validatedData = $request->validate([
<<<<<<< HEAD
                //'ec_font_url' => 'required|mimes:ttf,otf|max:2048'
            ]);
            $disk = Storage::disk('s3'); 
            if($request->hasFile('ec_font_url')){
                $title = $request->title;
                $events_custom_id = $request->events_custom_id;
                $template_component_id = $request->tp_id_font;
=======
            ]);
            $disk = Storage::disk('s3'); 
            $title = $request->ec_font_title;
            if($request->hasFile('ec_font_url')) {
                $events_custom_event_id = $request->events_custom_event_id;
>>>>>>> develop
                // Create image name
                $font_file = $request->file('ec_font_url');
                $name = $font_file->getClientOriginalName();
                // Define the new path to image
<<<<<<< HEAD
                $newFilePath = '/EventsCustoms/'.$template_component_id.'/'.$events_custom_id.'/'.$name;
=======
                $newFilePath = '/events/'.$events_custom_event_id.'/fonts/'.$title.'/'.$name;
>>>>>>> develop
                // Upload the new image
                $disk->put($newFilePath, $font_file, 'public');
                // Put in database
                $font_file = $newFilePath;
            }
            $response = array(
                'status' => 'success',
                'msg' => 'Font file send to the server'
            );
            return response()->json($response);
        }
        else {
            return 'no';
        }
    }
<<<<<<< HEAD
=======

    /**
     * Delete a file.
     *
     * @param  string $font_url
     * @return \Illuminate\Http\Response
     */
    public function deleteFile($events_custom_event_id, $font_title, $font_name)
    {
        $disk = Storage::disk('s3'); 
        $font_url = '/events/'.$events_custom_event_id.'/fonts/'.$font_title.'/'.$font_name; 
        $disk->delete($font_url);
        $response = array(
            'status' => 'success',
            'msg' => 'Font file has been deleted'
        );
        return response()->json($response);
    }
    
>>>>>>> develop
}