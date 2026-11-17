<?php
namespace App\Services;
use App\Models\Book;
use Illuminate\Support\Facades\DB;
class FinanceService {
 public function totals(Book $book, ?string $start=null, ?string $end=null): array {
  $base=$book->transactions();
  if($start) $base->whereDate('transaction_date','>=',$start);
  if($end) $base->whereDate('transaction_date','<=',$end);
  $income=(string)(clone $base)->where('type','income')->sum('amount');
  $expense=(string)(clone $base)->where('type','expense')->sum('amount');
  $prior=$start ? $book->transactions()->whereDate('transaction_date','<',$start)->selectRaw("COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END),0) as net")->value('net') : 0;
  $opening=round((float)$book->opening_balance+(float)$prior,2);
  return ['opening'=>$opening,'income'=>(float)$income,'expense'=>(float)$expense,'closing'=>round($opening+(float)$income-(float)$expense,2)];
 }
}
