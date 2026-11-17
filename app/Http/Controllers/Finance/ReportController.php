<?php
namespace App\Http\Controllers\Finance;
use App\Http\Controllers\Controller;
use App\Services\FinanceService;
use Illuminate\Http\Request;
class ReportController extends Controller {
 public function index(Request $r,FinanceService $finance){$book=$r->user()->activeBooks()->findOrFail(session('active_book_id'));$v=$r->validate(['from'=>'nullable|date','to'=>'nullable|date|after_or_equal:from']);$from=$v['from']??now()->startOfMonth()->toDateString();$to=$v['to']??now()->toDateString();$items=$book->transactions()->with('category')->whereBetween('transaction_date',[$from,$to])->orderBy('transaction_date')->get();$totals=$finance->totals($book,$from,$to);return view('finance.reports.index',compact('book','from','to','items','totals'));}
 public function csv(Request $r,FinanceService $finance){$book=$r->user()->activeBooks()->findOrFail(session('active_book_id'));$v=$r->validate(['from'=>'required|date','to'=>'required|date|after_or_equal:from']);$items=$book->transactions()->with('category')->whereBetween('transaction_date',[$v['from'],$v['to']])->orderBy('transaction_date')->cursor();return response()->streamDownload(function()use($items){$out=fopen('php://output','w');fputcsv($out,['Tanggal','Transaksi','Jenis','Kategori','Nominal (IDR)']);foreach($items as $t)fputcsv($out,[$t->transaction_date->format('Y-m-d'),"'".$t->title,$t->type,$t->category?->name,$t->amount]);fclose($out);},'finora-laporan.csv',['Content-Type'=>'text/csv; charset=UTF-8']);}
}
