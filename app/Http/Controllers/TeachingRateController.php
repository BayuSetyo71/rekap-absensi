<?php

namespace App\Http\Controllers;

use App\Models\TeachingRate;
use App\Models\Unit;
use Illuminate\Http\Request;

class TeachingRateController extends Controller
{
    /**
     * Tampilkan daftar matriks tarif honor mengajar per unit & mata pelajaran
     */
    public function index(Request $request)
    {
        $unitFilter = $request->get('unit_id');
        $search = $request->get('search');

        $query = TeachingRate::with('unit')->orderBy('unit_id', 'asc')->orderBy('subject_name', 'asc');

        if ($unitFilter) {
            $query->where('unit_id', $unitFilter);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('subject_name', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $rates = $query->paginate(20)->withQueryString();
        $units = Unit::where('is_active', true)->orderBy('id', 'asc')->get();

        // Rekap KPI Tarif
        $totalRates = TeachingRate::count();
        $activeRates = TeachingRate::where('is_active', true)->count();
        $unitsCount = $units->count();

        return view('teaching-rates.index', compact(
            'rates',
            'units',
            'unitFilter',
            'search',
            'totalRates',
            'activeRates',
            'unitsCount'
        ));
    }

    /**
     * Simpan tarif honor mengajar baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'unit_id' => 'required|exists:units,id',
            'subject_name' => 'required|string|max:150',
            'rate_per_hour' => 'required|numeric|min:0',
            'rate_type' => 'required|in:per_hour,per_session',
            'notes' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ], [
            'unit_id.required' => 'Jenjang / Unit sekolah wajib dipilih.',
            'subject_name.required' => 'Nama mata pelajaran atau DEFAULT wajib diisi.',
            'rate_per_hour.required' => 'Nominal honor per jam wajib diisi.',
            'rate_per_hour.numeric' => 'Nominal honor harus berupa angka.',
        ]);

        $subjectName = trim($request->subject_name);
        $isActive = $request->has('is_active') ? (bool)$request->is_active : true;

        // Cek duplikasi tarif di unit dan mapel yang sama
        $exists = TeachingRate::where('unit_id', $request->unit_id)
            ->whereRaw('LOWER(TRIM(subject_name)) = ?', [strtolower($subjectName)])
            ->first();

        if ($exists) {
            return redirect()->back()->with('error', "Tarif untuk mapel '{$subjectName}' di jenjang tersebut sudah ada.")->withInput();
        }

        TeachingRate::create([
            'unit_id' => $request->unit_id,
            'subject_name' => $subjectName,
            'rate_per_hour' => $request->rate_per_hour,
            'rate_type' => $request->rate_type,
            'notes' => $request->notes,
            'is_active' => $isActive,
        ]);

        return redirect()->route('teaching-rates.index')->with('success', 'Tarif honor mengajar berhasil ditambahkan.');
    }

    /**
     * Ambil data tarif untuk AJAX Edit
     */
    public function edit(TeachingRate $teachingRate)
    {
        return response()->json([
            'status' => 'success',
            'data' => $teachingRate->load('unit'),
        ]);
    }

    /**
     * Perbarui data tarif honor
     */
    public function update(Request $request, TeachingRate $teachingRate)
    {
        $request->validate([
            'unit_id' => 'required|exists:units,id',
            'subject_name' => 'required|string|max:150',
            'rate_per_hour' => 'required|numeric|min:0',
            'rate_type' => 'required|in:per_hour,per_session',
            'notes' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ], [
            'unit_id.required' => 'Jenjang / Unit sekolah wajib dipilih.',
            'subject_name.required' => 'Nama mata pelajaran wajib diisi.',
            'rate_per_hour.required' => 'Nominal honor per jam wajib diisi.',
        ]);

        $subjectName = trim($request->subject_name);

        // Cek duplikasi dengan ID lain
        $exists = TeachingRate::where('unit_id', $request->unit_id)
            ->whereRaw('LOWER(TRIM(subject_name)) = ?', [strtolower($subjectName)])
            ->where('id', '!=', $teachingRate->id)
            ->first();

        if ($exists) {
            return redirect()->back()->with('error', "Tarif untuk mapel '{$subjectName}' di jenjang tersebut sudah ada.")->withInput();
        }

        $teachingRate->update([
            'unit_id' => $request->unit_id,
            'subject_name' => $subjectName,
            'rate_per_hour' => $request->rate_per_hour,
            'rate_type' => $request->rate_type,
            'notes' => $request->notes,
            'is_active' => $request->has('is_active') ? (bool)$request->is_active : $teachingRate->is_active,
        ]);

        return redirect()->route('teaching-rates.index')->with('success', 'Tarif honor mengajar berhasil diperbarui.');
    }

    /**
     * Hapus aturan tarif
     */
    public function destroy(TeachingRate $teachingRate)
    {
        $teachingRate->delete();
        return redirect()->route('teaching-rates.index')->with('success', 'Tarif honor mengajar berhasil dihapus.');
    }

    /**
     * Toggle status aktif
     */
    public function toggleActive(TeachingRate $teachingRate)
    {
        $teachingRate->is_active = !$teachingRate->is_active;
        $teachingRate->save();

        return response()->json([
            'status' => 'success',
            'is_active' => $teachingRate->is_active,
            'message' => 'Status aktif tarif berhasil diperbarui.',
        ]);
    }
}
