<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\TransferController;

Route::get('/', function () {
    // return view('auth.login');
    return redirect()->route('login');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('dashboard', function () {
   return view('layouts.master');
});



Route::group(['middleware' => 'auth'], function () {
    Route::resource('categories','CategoryController');
    Route::get('/apiCategories','CategoryController@apiCategories')->name('api.categories');
    Route::get('/exportCategoriesAll','CategoryController@exportCategoriesAll')->name('exportPDF.categoriesAll');
    Route::get('/exportCategoriesAllExcel','CategoryController@exportExcel')->name('exportExcel.categoriesAll');

    Route::resource('customers','CustomerController');
    Route::get('/apiCustomers','CustomerController@apiCustomers')->name('api.customers');
    Route::post('/importCustomers','CustomerController@ImportExcel')->name('import.customers');
    Route::get('/exportCustomersAll','CustomerController@exportCustomersAll')->name('exportPDF.customersAll');
    Route::get('/exportCustomersAllExcel','CustomerController@exportExcel')->name('exportExcel.customersAll');

    Route::resource('sales','SaleController');
    Route::get('/apiSales','SaleController@apiSales')->name('api.sales');
    Route::post('/importSales','SaleController@ImportExcel')->name('import.sales');
    Route::get('/exportSalesAll','SaleController@exportSalesAll')->name('exportPDF.salesAll');
    Route::get('/exportSalesAllExcel','SaleController@exportExcel')->name('exportExcel.salesAll');

    Route::resource('suppliers','SupplierController');
    Route::get('/apiSuppliers','SupplierController@apiSuppliers')->name('api.suppliers');
    Route::post('/importSuppliers','SupplierController@ImportExcel')->name('import.suppliers');
    Route::get('/exportSupplierssAll','SupplierController@exportSuppliersAll')->name('exportPDF.suppliersAll');
    Route::get('/exportSuppliersAllExcel','SupplierController@exportExcel')->name('exportExcel.suppliersAll');

    Route::resource('products','ProductController');
    Route::get('/apiProducts','ProductController@apiProducts')->name('api.products');

    Route::resource('productsOut','ProductOutController');
    Route::get('/apiProductsOut','ProductOutController@apiProductsOut')->name('api.productsOut');
    Route::get('/exportProductOutAll','ProductOutController@exportProductOutAll')->name('exportPDF.productOutAll');
    Route::get('/exportProductOutAllExcel','ProductOutController@exportExcel')->name('exportExcel.productOutAll');
    Route::get('/exportProductOut/','ProductOutController@exportProductOut')->name('exportPDF.productOut');
    Route::get('/checkAvailable/{id}','ProductOutController@checkAvailable')->name('checkAvailable');

    Route::resource('productsIn','ProductInController');
    Route::get('/apiProductsIn','ProductInController@apiProductsIn')->name('api.productsIn');
    Route::get('/exportProductInAll','ProductInController@exportProductInAll')->name('exportPDF.productInAll');
    Route::get('/exportProductInAllExcel','ProductInController@exportExcel')->name('exportExcel.productInAll');

    Route::resource('users','UserController');
    Route::get('/apiUsers','UserController@apiUsers')->name('api.users');

    Route::resource('company','CompanyController');

    Route::get('/stock', 'StockController@index')->name('stock.index');
    Route::post('/stock', 'StockController@store')->name('stock.store');
    Route::put('/stock/{id}', 'StockController@update')->name('stock.update');
    Route::delete('/stock/{id}', 'StockController@destroy')->name('stock.destroy');

    Route::get('/transfer', [TransferController::class, 'index'])->name('transfer.index');
    Route::get('/transfer/supplier', [TransferController::class, 'getSupplierByBarcode']);
    Route::get('/transfer/products', [TransferController::class, 'getSupplierProducts']);
    Route::post('/transfer/submit', [TransferController::class, 'transfer']);
    


    Route::prefix('inventory')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('/product-in', [InventoryController::class, 'productIn'])->name('inventory.product.in');
        Route::get('/product-out', [InventoryController::class, 'productOut'])->name('inventory.product.out');
        Route::get('/inventory/add-product', [InventoryController::class, 'addProduct'])->name('inventory.add.product');
        Route::post('/inventory/add-product', [InventoryController::class, 'storeProduct'])->name('inventory.store.product');
        Route::get('/settings', [InventoryController::class, 'settings'])->name('inventory.settings');
    });

});

Route::get('/pdf-test', function () {
    $pdf = PDF::loadHTML('<h1>Hello from Laravel 5.8!</h1>');
    return $pdf->download('test.pdf');
});