<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Product Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .container {
            max-width: 1200px;
        }
        .product-form {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .products-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .table th {
            background: #007bff;
            color: white;
            border: none;
        }
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        .edit-btn {
            cursor: pointer;
            color: #007bff;
            margin-right: 10px;
        }
        .edit-btn:hover {
            color: #0056b3;
        }
        .save-btn {
            cursor: pointer;
            color: #28a745;
            margin-right: 10px;
        }
        .save-btn:hover {
            color: #1e7e34;
        }
        .total-row {
            background: #e9ecef;
            font-weight: bold;
        }
        .alert {
            margin-top: 1rem;
        }
        .loading {
            display: none;
        }
        .edit-input {
            width: 100%;
            border: 1px solid #ced4da;
            border-radius: 4px;
            padding: 0.375rem 0.75rem;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1 class="text-center mb-4">
            <i class="fas fa-box"></i> Product Manager
        </h1>

        <div id="alert-container"></div>

        <div class="product-form">
            <h3 class="mb-3">Add New Product</h3>
            <form id="productForm">
                @csrf
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="name" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                            <div class="invalid-feedback" id="name-error"></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="quantity" class="form-label">Quantity in Stock</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" min="0" required>
                            <div class="invalid-feedback" id="quantity-error"></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="price" class="form-label">Price per Item</label>
                            <input type="number" class="form-control" id="price" name="price" min="0" step="0.01" required>
                            <div class="invalid-feedback" id="price-error"></div>
                        </div>
                    </div>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <span class="loading spinner-border spinner-border-sm me-2" role="status"></span>
                        <i class="fas fa-plus me-2"></i>Add Product
                    </button>
                </div>
            </form>
        </div>

        <div class="products-table">
            <h3 class="p-3 mb-0 border-bottom">Products Inventory</h3>
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Quantity in Stock</th>
                            <th>Price per Item</th>
                            <th>DateTime Submitted</th>
                            <th>Total Value</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="products-table-body">
                        <!-- Products will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            loadProducts();

            // Form submission
            $('#productForm').on('submit', function(e) {
                e.preventDefault();
                
                $('.loading').show();
                $('button[type="submit"]').prop('disabled', true);

                // Clear previous errors
                $('.form-control').removeClass('is-invalid');
                $('.invalid-feedback').text('');

                $.ajax({
                    url: '{{ route("products.store") }}',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            showAlert('success', response.data.message);
                            $('#productForm')[0].reset();
                            loadProducts();
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            Object.keys(errors).forEach(field => {
                                $(`#${field}`).addClass('is-invalid');
                                $(`#${field}-error`).text(errors[field][0]);
                            });
                        } else {
                            showAlert('danger', 'An error occurred while adding the product.');
                        }
                    },
                    complete: function() {
                        $('.loading').hide();
                        $('button[type="submit"]').prop('disabled', false);
                    }
                });
            });

            function loadProducts() {
                $.ajax({
                    url: '{{ route("products.get") }}',
                    method: 'GET',
                    success: function(response) {
                        let tableBody = '';

                        if (response.products && response.products.length > 0) {
                            response.products.forEach(function(product) {
                                const createdAt = new Date(product.created_at).toLocaleString();
                                
                                tableBody += `
                                    <tr data-id="${product.id}">
                                        <td class="editable" data-field="name">${product.name}</td>
                                        <td class="editable" data-field="quantity">${product.quantity}</td>
                                        <td class="editable" data-field="price">$${parseFloat(product.price).toFixed(2)}</td>
                                        <td>${createdAt}</td>
                                        <td class="total-value">$${parseFloat(product.total_value).toFixed(2)}</td>
                                        <td>
                                            <i class="fas fa-edit edit-btn" title="Edit"></i>
                                        </td>
                                    </tr>
                                `;
                            });
                        }

                        // Add total row
                        const totalSum = response.total_sum || 0;
                        tableBody += `
                            <tr class="total-row">
                                <td colspan="4" class="text-end">Grand Total:</td>
                                <td>$${parseFloat(totalSum).toFixed(2)}</td>
                                <td></td>
                            </tr>
                        `;

                        $('#products-table-body').html(tableBody);
                    },
                    error: function() {
                        showAlert('danger', 'Failed to load products.');
                    }
                });
            }

            // Edit product
            $(document).on('click', '.edit-btn', function() {
                const row = $(this).closest('tr');
                const productId = row.data('id');
                
                row.find('.editable').each(function() {
                    const cell = $(this);
                    const field = cell.data('field');
                    let value = cell.text();
                    
                    if (field === 'price') {
                        value = value.replace('$', '');
                    }
                    
                    const inputType = field === 'quantity' ? 'number' : (field === 'price' ? 'number' : 'text');
                    const stepAttr = field === 'price' ? 'step="0.01"' : '';
                    const minAttr = (field === 'quantity' || field === 'price') ? 'min="0"' : '';
                    
                    const input = $(`<input type="${inputType}" class="edit-input" value="${value}" ${stepAttr} ${minAttr}>`);
                    cell.html(input);
                });

                $(this).removeClass('fa-edit edit-btn').addClass('fa-save save-btn').attr('title', 'Save');
            });

            // Save edit
            $(document).on('click', '.save-btn', function() {
                const row = $(this).closest('tr');
                const productId = row.data('id');
                const data = {};

                row.find('.editable').each(function() {
                    const cell = $(this);
                    const field = cell.data('field');
                    const input = cell.find('.edit-input');
                    data[field] = input.val();
                });

                $.ajax({
                    url: `{{ url('/products') }}/${productId}`,
                    method: 'PUT',
                    data: data,
                    success: function(response) {
                        if (response.success) {
                            showAlert('success', response.data.message);
                            loadProducts();
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            let errorMsg = 'Validation errors: ';
                            Object.keys(errors).forEach(field => {
                                errorMsg += errors[field][0] + ' ';
                            });
                            showAlert('danger', errorMsg);
                        } else {
                            showAlert('danger', 'Failed to update product.');
                        }
                        loadProducts(); 
                    }
                });
            });

            function showAlert(type, message) {
                const alertHtml = `
                    <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                $('#alert-container').html(alertHtml);
                
                setTimeout(() => {
                    $('.alert').alert('close');
                }, 5000);
            }

            setInterval(loadProducts, 30000);
        });
    </script>
</body>
</html>