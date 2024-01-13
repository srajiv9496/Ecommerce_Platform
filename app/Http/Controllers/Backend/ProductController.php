<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\ProductDataTable;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ChildCategory;
use App\Models\Product;
use App\Models\ProductImageGallery;
use App\Models\ProductVariant;
use App\Models\SubCategory;
use App\Traits\ImageUploadTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Str;

class ProductController extends Controller
{
    use ImageUploadTrait;
    /**
     * Display a listing of the resource.
     */
    public function index(ProductDataTable $dataTable)
    {
        return $dataTable->render('admin.product.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        $brands = Brand::all();
        return view('admin.product.create', compact('categories','brands'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'image' => ['required', 'image', 'max:3000'],
            'name' => ['required', 'max:200'],
            'category' => 'required',
            'brand' => 'required',
            'price' => 'required',
            'qty' => 'required',
            'short_description' =>['required','max:600'],
            'long_description' => 'required',
            'product_type' => 'required',
            'seo_title' => ['nullable','max:200'],
            'seo_description' => ['nullable','max:250'],
            'status' => ['required']    
        ]);
        
        /**Handle image uplaod */
        $imagePath = $this->uploadImage($request, 'image', 'uploads');
        $products = new Product();
        $products->thumb_image = $imagePath;
        $products->name = $request->name;
        $products->slug = Str::slug($request->name);
        $products->vendor_id = Auth::user()->vendor->id;
        $products->category_id = $request->category;
        $products->sub_category_id = $request->sub_category;
        $products->child_category_id = $request->child_category;    
        $products->brand_id = $request->brand;
        $products->qty = $request->qty;
        $products->short_description = $request->short_description;
        $products->long_description = $request->long_description;
        $products->video_link = $request->video_link;
        $products->sku = $request->sku;
        $products->price = $request->price;
        $products->offer_price = $request->offer_price;
        $products->offer_start_date = $request->offer_start_date;   
        $products->offer_end_date = $request->offer_end_date;   
        $products->product_type = $request->product_type;
        $products->status = $request->status;
        $products->is_approved = 1;
        $products->seo_title = $request->seo_title; 
        $products->seo_description = $request->seo_description;
        $products->save();

        toastr()->success('Product has been created successfully!', 'success');

        return redirect()->route('admin.product.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
    //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $product = Product::findOrFail($id);
        $subCategories = SubCategory::where('category_id', $product->category_id)->get();
        $childCategories = ChildCategory::where('sub_category_id', $product->sub_category_id)->get();
        $brands = Brand::all();
        $categories = Category::all();
        return view('admin.product.edit', compact('product','categories', 'brands', 'subCategories', 'childCategories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // dd($request->all());
        $request->validate([
            'image' => ['nullable', 'image', 'max:3000'],
            'name' => ['required', 'max:200'],
            'category' => 'required',
            'brand' => 'required',
            'price' => 'required',
            'qty' => 'required',
            'short_description' =>['required','max:600'],
            'long_description' => 'required',
            'product_type' => 'required',
            'seo_title' => ['nullable','max:200'],
            'seo_description' => ['nullable','max:250'],
            'status' => ['required']    
        ]);
        

        $products = Product::findOrFail($id);

        /**Handle image uplaod */
        $imagePath = $this->updateImage($request, 'image', 'uploads', $products->thumb_image);

        $products->thumb_image = empty(!$imagePath) ? $imagePath : $products->thumb_image;
        $products->name = $request->name;
        $products->slug = Str::slug($request->name);
        $products->vendor_id = Auth::user()->vendor->id;
        $products->category_id = $request->category;
        $products->sub_category_id = $request->sub_category;
        $products->child_category_id = $request->child_category;    
        $products->brand_id = $request->brand;
        $products->qty = $request->qty;
        $products->short_description = $request->short_description;
        $products->long_description = $request->long_description;
        $products->video_link = $request->video_link;
        $products->sku = $request->sku;
        $products->price = $request->price;
        $products->offer_price = $request->offer_price;
        $products->offer_start_date = $request->offer_start_date;   
        $products->offer_end_date = $request->offer_end_date;   
        $products->product_type = $request->product_type;
        $products->status = $request->status;
        $products->is_approved = 1;
        $products->seo_title = $request->seo_title; 
        $products->seo_description = $request->seo_description;
        $products->save();

        toastr()->success('Product has been Updated successfully!', 'success');

        return redirect()->route('admin.product.index');    
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        /** Delete the main product image */
        $this->deleteImage($product->thumb_image);

        /** Delete product gallery images */
        $galleryImages = ProductImageGallery::where('product_id', $product->id)->get();
        foreach($galleryImages as $image){
           $this->deleteImage($image->image);
           $image->delete();
        }

        /** Delete product variants if exists */
        $variants = ProductVariant::where('product_id', $product->id)->get();

        foreach($variants as $variant){
            $variant->productVariantItems()->delete();
            $variant->delete();
        }

        $product->delete();
        
        return response(['status' => 'success', 'message' => 'Product has been deleted successfully!']);
    }

    /**
     * Get all sub categories.
     */
    public function getSubCategories(Request $request){
        $subCategories = SubCategory::where('category_id', $request->id)->get();

        return $subCategories;
    }

    public function getChildCategories(Request $request){
        $childCategories = ChildCategory::where('sub_category_id', $request->id)->get();

        return $childCategories;
    }

    public function changeStatus(Request $request){
        $product = Product::findOrFail($request->id);
        $product->status = $request->status == 'true' ? 1 : 0;
        $product->save();

        return response(['status' => 'success', 'message' => 'Status has been successfully updated!']);
    }
}
