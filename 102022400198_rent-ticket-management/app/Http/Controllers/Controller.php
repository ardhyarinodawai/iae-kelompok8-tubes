<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 * version="1.0.0",
 * title="Service Manajemen Tiket Tenant API",
 * description="Dokumentasi API Terintegrasi untuk Manajemen Tiket Keluhan Properti (Dawai)",
 * @OA\Contact(
 * name="Contact the developer"
 * )
 * )
 *
 * @OA\Server(
 * url="http://localhost:8002",
 * description="API Main Server Host Lokal"
 * )
 * * @OA\SecurityScheme(
 * securityScheme="ApiKeyAuth",
 * type="apiKey",
 * in="header",
 * name="X-API-KEY",
 * description="Masukkan NIM Anda (102022400198) untuk membuka akses API"
 * )
 */
abstract class Controller
{
    //
}