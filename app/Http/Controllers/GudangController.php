<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KategoriModel;
use App\Models\SupplierModel;
use App\Models\RakModel;
use App\Models\BarangModel;
use Illuminate\Support\Facades\Validator;

class GudangController extends Controller
{
    // Kategori

    public function indexKategori()
    {
        $kategori = KategoriModel::all();

        return response()->json([
            'success' => true,
            'message' => 'List semua kategori',
            'data' => $kategori
        ], 200);
    }

    public function createKategori()
    {
        //
    }

    public function storeKategori(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:100',
        ]);

        $kategori = KategoriModel::create([
            'nama_kategori' => $request->nama_kategori,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil dibuat',
            'data' => $kategori
        ], 201);
    }

    public function showKategori(KategoriModel $kategori)
    {

        if (!$kategori) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail kategori',
            'data' => $kategori
        ], 200);
    }

    public function editKategori($id)
    {
        //
    }

    public function updateKategori(Request $request, $id)
    {
        $kategori = KategoriModel::find($id);

        if (!$kategori) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori tidak ditemukan',
            ], 404);
        }

        $request->validate([
            'nama_kategori' => 'required|string|max:100',
        ]);

        $kategori->update([
            'nama_kategori' => $request->nama_kategori,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil diupdate',
            'data' => $kategori
        ], 200);
    }

    public function destroyKategori($id)
    {
        $kategori = KategoriModel::find($id);

        if (!$kategori) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori tidak ditemukan',
            ], 404);
        }

        $kategori->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil dihapus',
        ], 200);
    }

     // Supplier

     public function indexSupplier()
     {
         $supplier = SupplierModel::all();

         return response()->json([
             'success' => true,
             'message' => 'List semua Supplier',
             'data' => $supplier
         ], 200);
     }

     public function createSupplier()
     {
         //
     }

     public function storeSupplier(Request $request)
     {
         $request->validate([
             'nama_supplier' => 'required|string|max:100',
             'kontak' => 'required|string',
             'alamat' => 'required|string',
         ]);

         $supplier = SupplierModel::create([
             'nama_supplier' => $request->nama_supplier,
             'kontak' => $request->kontak,
             'alamat' => $request->alamat,
         ]);

         return response()->json([
             'success' => true,
             'message' => 'Supplier berhasil dibuat',
             'data' => $supplier
         ], 201);
     }

     public function showSupplier($id)
     {

         $supplier = SupplierModel::find($id);

         if (!$supplier) {
             return response()->json([
                 'success' => false,
                 'message' => 'Supplier tidak ditemukan',
             ], 404);
         }

         return response()->json([
             'success' => true,
             'message' => 'Detail Supplier',
             'data' => $supplier
         ], 200);
     }

     public function editSupplier($id)
     {
         //
     }

     public function updateSupplier(Request $request, $id)
     {
        $supplier = SupplierModel::findOrFail($id);

         if (!$supplier) {
             return response()->json([
                 'success' => false,
                 'message' => 'Supplier tidak ditemukan',
             ], 404);
         }

         $request->validate([
             'nama_supplier' => 'required|string|max:100',
             'kontak' => 'required|string',
             'alamat' => 'required|string',
         ]);

         $supplier->update($request->all());

         return response()->json([
             'success' => true,
             'message' => 'Supplier berhasil diupdate',
             'data' => $supplier
         ], 200);
     }

     public function destroySupplier($id)
     {
         $supplier = SupplierModel::find($id);

         if (!$supplier) {
             return response()->json([
                 'success' => false,
                 'message' => 'Supplier tidak ditemukan',
             ], 404);
         }

         $supplier->delete();

         return response()->json([
             'success' => true,
             'message' => 'Supplier berhasil dihapus',
         ], 200);
     }

      // Rak

      public function indexRak()
      {
          $rak = RakModel::all();

          return response()->json([
              'success' => true,
              'message' => 'List semua rak',
              'data' => $rak
          ], 200);
      }

      public function createRak()
      {
          //
      }

      public function storeRak(Request $request)
      {
          $request->validate([
              'kode_rak' => 'required|string|max:20',
              'nama_rak' => 'required|string|max:100',
              'lokasi_rak' => 'required|string',
          ]);

          $rak = RakModel::create([
              'kode_rak' => $request->kode_rak,
              'nama_rak' => $request->nama_rak,
              'lokasi_rak' => $request->lokasi_rak,
          ]);

          return response()->json([
              'success' => true,
              'message' => 'Rak berhasil dibuat',
              'data' => $rak
          ], 201);
      }

      public function showRak($id)
      {

          $rak = RakModel::find($id);

          if (!$rak) {
              return response()->json([
                  'success' => false,
                  'message' => 'Rak tidak ditemukan',
              ], 404);
          }

          return response()->json([
              'success' => true,
              'message' => 'Detail Rak',
              'data' => $rak
          ], 200);
      }

      public function editRak($id)
      {
          //
      }

      public function updateRak(Request $request, $id)
      {
          $rak = RakModel::find($id);

          if (!$rak) {
              return response()->json([
                  'success' => false,
                  'message' => 'Rak tidak ditemukan',
              ], 404);
          }

          $request->validate([
              'kode_rak' => 'required|string|max:20',
              'nama_rak' => 'required|string|max:100',
              'lokasi_rak' => 'required|string',
          ]);

          $rak->update([
              'kode_rak' => $request->kode_rak,
              'nama_rak' => $request->nama_rak,
              'lokasi_rak' => $request->lokasi_rak,
          ]);

          return response()->json([
              'success' => true,
              'message' => 'Rak berhasil diupdate',
              'data' => $rak
          ], 200);
      }

      public function destroyRak($id)
      {
          $rak = RakModel::find($id);

          if (!$rak) {
              return response()->json([
                  'success' => false,
                  'message' => 'Rak tidak ditemukan',
              ], 404);
          }

          $rak->delete();

          return response()->json([
              'success' => true,
              'message' => 'Rak berhasil dihapus',
          ], 200);
      }

      // Barang

      public function index()
    {
        $barang = BarangModel::with(['kategori','supplier'])->get();

        return response()->json([
            'success' => true,
            'message' => 'List semua barang',
            'data' => $barang
        ], 200);
    }

    public function show($id)
    {
        $barang = BarangModel::with(['kategori','supplier'])->find($id);

        if (!$barang) {
            return response()->json([
                'success' => false,
                'message' => 'Barang tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail barang',
            'data' => $barang
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'id_kategori' => 'required|exists:tb_kategori,id',
            'id_supplier' => 'required|exists:tb_supplier,id',
            'nama_barang' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'harga' => 'required|numeric|min:0',
        ]);

        // Handle file upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('barang_images', 'public');
        }

        $barang = BarangModel::create([
            'image' => $imagePath,
            'id_kategori' => $request->id_kategori,
            'id_supplier' => $request->id_supplier,
            'nama_barang' => $request->nama_barang,
            'deskripsi' => $request->deskripsi,
            'harga' => $request->harga,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil dibuat',
            'data' => $barang
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $barang = BarangModel::with(['kategori','supplier'])->find($id);

        if (!$barang) {
            return response()->json([
                'success' => false,
                'message' => 'Barang tidak ditemukan',
            ], 404);
        }

        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'id_kategori' => 'required|exists:tb_kategori,id',
            'id_supplier' => 'required|exists:tb_supplier,id',
            'nama_barang' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'harga' => 'required|numeric|min:0',
        ]);

        // Handle file upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('barang_images', 'public');
            $barang->update(['image' => $imagePath]);
        }

        $barang->update([
            'id_kategori' => $request->id_kategori,
            'id_supplier' => $request->id_supplier,
            'nama_barang' => $request->nama_barang,
            'deskripsi' => $request->deskripsi,
            'harga' => $request->harga
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil diupdate',
            'data' => $barang
        ], 200);
    }

    public function destroy($id)
    {
        $barang = BarangModel::find($id);

        if (!$barang) {
            return response()->json([
                'success' => false,
                'message' => 'Barang tidak ditemukan',
            ], 404);
        }

        if ($barang->image) {
            \Storage::disk('public')->delete($barang->image);
        }

        $barang->delete();

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil dihapus',
        ], 200);
    }
}
