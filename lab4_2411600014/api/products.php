<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$products = [
    [
        "sku" => "PROD-001",
        "name" => "Wireless Ergonomic Mouse",
        "category" => "Electronics",
        "price" => 29.99,
        "quantity" => 45,
        "minStock" => 10
    ],
    [
        "sku" => "PROD-002",
        "name" => "Mechanical RGB Keyboard",
        "category" => "Electronics",
        "price" => 89.99,
        "quantity" => 8,
        "minStock" => 15
    ],
    [
        "sku" => "PROD-003",
        "name" => "Standing Desk Converter",
        "category" => "Furniture",
        "price" => 199.99,
        "quantity" => 12,
        "minStock" => 5
    ],
    [
        "sku" => "PROD-004",
        "name" => "Ergonomic Mesh Chair",
        "category" => "Furniture",
        "price" => 249.99,
        "quantity" => 3,
        "minStock" => 5
    ],
    [
        "sku" => "PROD-005",
        "name" => "USB-C Multi-Port Hub",
        "category" => "Electronics",
        "price" => 39.99,
        "quantity" => 0,
        "minStock" => 10
    ],
    [
        "sku" => "PROD-006",
        "name" => "27-inch 4K Monitor",
        "category" => "Electronics",
        "price" => 349.99,
        "quantity" => 18,
        "minStock" => 5
    ],
    [
        "sku" => "PROD-007",
        "name" => "Noise Canceling Headphones",
        "category" => "Electronics",
        "price" => 179.99,
        "quantity" => 50,
        "minStock" => 12
    ]
];

echo json_encode($products);