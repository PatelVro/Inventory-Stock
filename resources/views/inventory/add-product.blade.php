@extends('layouts.master')
@section('content')


<a href="{{ route('inventory.index') }}" class="btn btn-secondary mb-3">
    &larr; Back
</a>
            <form  id="form-item" method="post" class="form-horizontal" data-toggle="validator" enctype="multipart/form-data" >
             

                    <input type="hidden" id="id" name="id">


                    <div class="box-body">
                        <div class="form-group">
                            <label>Select Category</label><br>
                            <select class="form-control" name="category" id="category">
                                <option value="">Select Category</option>
                                <option value="apple">Apple</option>
                                <option value="samsung">Samsung</option>
                            </select>
                            <span class="help-block with-errors"></span>
                        </div>

                        <div class="form-group">
                            <label>Add Products</label><br>
                            <input type="text" class="form-control" id="products" name="products" required>
                            <span class="help-block with-errors"></span>
                        </div>

                        <div class="form-group">
                            <label>Supplier</label><br>
                            <input type="text" class="form-control" id="location" name="location" required>
                        </div>

                        <div class="form-group">
                            <label>Quantity</label>
                            <input type="text" class="form-control" id="qty" name="qty" required>
                            <span class="help-block with-errors"></span>
                        </div>

                        

                    </div>
                    <!-- /.box-body -->

                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>

            </form>
   @endsection