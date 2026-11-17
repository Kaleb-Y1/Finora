<?php
namespace App\Http\Controllers\Finance;
use App\Http\Controllers\Controller;
use App\Services\FinanceService;
use Illuminate\Http\Request;
class DashboardController extends Controller {
 public function index(Request $r,FinanceService $finance){$book=$r->user()->activeBooks()->find(session('active_book_id'));if(!$book)return redirect()->route('books.create');$totals=$finance->totals($book);$month=$finance->totals($book,now()->startOfMonth()->toDateString(),now()->toDateString());$recent=$book->transactions()->with('category')->latest('transaction_date')->limit(7)->get();return view($r->user()->isOrangTua()?'finance.dashboard.senior':'finance.dashboard.general',compact('book','totals','month','recent'));}
}
