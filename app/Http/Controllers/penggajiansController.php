<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Penggajian;
use App\Tunjangan_pegawai;
use App\Tunjangans;
use App\Pegawai;
use App\Jabatan;
use App\Golongan;
use App\Lembur_pegawai;
use App\Kategori_lembur;
use Input;
use Auth;
use SUM;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

class penggajiansController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('Pegawai');
    }
    public function index()
    {
        //
        $gajian = Penggajian::all();
        return view('penggajians.index', compact('gajian'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        $tunjangan = Tunjangan_pegawai::all();
        return view('penggajians.create',compact('tunjangan'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
       $inputgaji=Input::all();

       $tunjangan_pegawai = Tunjangan_pegawai::where('id',$inputgaji['tunjangan_pegawai_id'])->first();

       $WPenggajian=Penggajian::where('tunjangan_pegawai_id',$tunjangan_pegawai->id)->first();

       $tunjangan = Tunjangans::where('id',$tunjangan_pegawai->Kode_tunjangan_id)->first();
       
       $pegawai = Pegawai::where('id',$tunjangan_pegawai->pegawai_id)->first();
       
       $kategori_lembur = Kategori_lembur::where('jabatan_id',$pegawai->jabatan_id)->where('golongan_id',$pegawai->golongan_id)->first();
       
       $lembur_pegawai = Lembur_pegawai::where('pegawai_id',$pegawai->id)->first();
       
       $jabatan = Jabatan::where('id',$pegawai->jabatan_id)->first();
       
       $golongan = Golongan::where('id',$pegawai->golongan_id)->first();
       

       $gaji = new Penggajian ;

       if (isset($WPenggajian)) {
           $error=true ;
           $tunjangan=Tunjangan_pegawai::paginate(10);
           return view('penggajians.create',compact('tunjangan','error'));
       }
       elseif (!isset($lembur_pegawai)) {
            $nol = 0;
            $gaji->Jumlah_jam_lembur= $nol;
            $gaji->Jumlah_uang_lembur = $nol;
            $gaji->Gaji_pokok = $jabatan->Besaran_uang + $golongan->Besaran_uang;
            $gaji->Total_gaji=($tunjangan->Jumlah_anak*$tunjangan->Besaran_uang)+($jabatan->Besaran_uang+$golongan->Besaran_uang);
            $gaji->Tanggal_pengambilan = date('d-m-y');
            $gaji->Status_pengambilan = Input::get('Status_pengambilan');
            $gaji->tunjangan_pegawai_id = Input::get('tunjangan_pegawai_id');
            $gaji->Petugas_penerima = Auth::user()->name;
            $gaji->save();
        }
        elseif(!isset($lembur_pegawai) || !isset($kategori_lembur))
        {
            $nol = 0;
            $gaji->Jumlah_jam_lembur= $nol;
            $gaji->Jumlah_uang_lembur = $nol;
            $gaji->Gaji_pokok=$jabatan->Besaran_uang+$golongan->Besaran_uang;
            $gaji->Total_gaji = ($tunjangan->Jumlah_anak*$tunjangan->Besaran_uang)+($jabatan->Besaran_uang+$golongan->Besaran_uang);
            $gaji->Tanggal_pengambilan = date('d-m-y');
            $gaji->Status_pengambilan = Input::get('Status_pengambilan');
            $gaji->tunjangan_pegawai_id = Input::get('tunjangan_pegawai_id');
            $gaji->Petugas_penerima = Auth::user()->name;
            $gaji->save();
            
        }
        else
        {
            $gaji->Jumlah_jam_lembur = ($lembur_pegawai->Jumlah_jam);
            $gaji->Jumlah_uang_lembur =($lembur_pegawai->Jumlah_jam)*($kategori_lembur->Besaran_uang);
            $gaji->Gaji_pokok=$jabatan->Besaran_uang+$golongan->Besaran_uang;
            $gaji->Total_gaji = ($lembur_pegawai->Jumlah_jam*$kategori_lembur->Besaran_uang)+($tunjangan->Jumlah_anak*$tunjangan->Besaran_uang)+($jabatan->Besaran_uang+$golongan->Besaran_uang);
            $gaji->Tanggal_pengambilan = date('d-m-y');
            $gaji->Status_pengambilan = Input::get('Status_pengambilan');
            $gaji->tunjangan_pegawai_id = Input::get('tunjangan_pegawai_id');
            $gaji->Petugas_penerima = Auth::user()->name;
            $gaji->save();
        }
        

        return redirect('penggajian');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        $tunjangpegawai = Tunjangan_pegawai::all();
        $jab = Jabatan::all();
        $gol = Golongan::all();
        $pegawai = Pegawai::all();
        $gaji = Penggajian::find($id);
        return view('tunjanganspegawai.edit', compact('tunjangpegawai', 'jab', 'gol', 'pegawai', 'gaji'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $gajiUpdate = Request::all();
        $gaji = Penggajian::find($id);
        $gaji->update($gajiUpdate);
        return redirect('penggajian');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        Penggajian::find($id)->delete();
        return redirect('penggajian');
    }
}
