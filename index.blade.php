
<!-- Symbol Pips val -->
<?php
    $symbolpips  
    = [ ['xauusd',2],
        ['xagusd',2],
        ['gold',0],
        ['xaueur',2],
        ['btcusd',0],
        ['ethusd',0],
        ['ltcusd',0],
        ['eurusd',4],
        ['usdjpy',2],
        ['audcad',4], 
        ['audchf',4],
        ['audjpy',2],
        ['audnzd',4],
        ['audusd',4],
        ['cadchf',4],
        ['cadjpy',2],
        ['chfjpy',2],
        ['euraud',4],
        ['eurcad',4], 
        ['nzdcad',4],
        ['nzdchf',4],
        ['nzdjpy',2],
        ['nzdusd',4],
        ['usdcad',4],
        ['usdchf',4],
        ['eurchf',4],
        ['eurczk',3],
        ['eurgbp',4],
        ['eurhuf',2],
        ['eurjpy',2],
        ['eurmxn',4],
        ['eurnok',4],
        ['eurnzd',4],
        ['eurpln',4],
        ['eursek',4],
        ['eursgd',4],
        ['eurtry',4],
        ['gbpaud',4],
        ['gbpcad',4],
        ['gbpchf',4],
        ['gbpjpy',2],
        ['gbpnok',4],
        ['gbpnzd',4],
        ['gbptry',4],
        ['gbpusd',4],
        ['noksek',4],
        ['usdcnh',4],
        ['usdczk',3],
        ['usddkk',4],
        ['usdhkd',4],
        ['usdhuf',2],
        ['usdils',4], 
        ['usdmxn',4],
        ['usdnok',4],
        ['usdpln',4],
        ['usdrub',4],
        ['usdsok',4],
        ['usdsgd',4],
        ['usdtry',4],
        ['usdzar',4], 

 
        
    ]
?>

  

<!-- $iTWRratePersentTabel = [] ;  -->
<?php
    //use Illuminate\Support\Facades\Http;
    use App\Models\order;
    use Illuminate\Support\Facades\Http;
    $SPASE = " ";
    $token = "CSV";
    $Total_pips = 0 ;
    $FerstDeposit = 0;
    $FerstDeposit_OK = -1;
    $Deposit = 0;
    $Withdrawal = 0;
    $HighestDeposit = 0;
    $HighestDeposi_date = " ";
    $Total_Profit = 0 ;
    $Total_Profit_Withdrawal_Deposit = 0;
    $TWRProfits = 0 ;
    $TWRrate = 1;
    $iTWRrate = 1;
    $iTWRratePersentTabel = [] ; 
     

    $query = "SELECT * FROM 'orders' WHERE 'login'='" .$id. "'" ;
    $orders = DB::select('select * from orders where login = :login ORDER BY closeTime ' , ['login' => $id]);
    $account_k = DB::select('select * from strategies where login = :login ' , ['login' => $id]);
    $rest_api_full = DB::select('select * from apis where type=\'MT4\' and connectin=\'rest\' ; ')[0]->url; 

    $rest_api_full_MT5 = DB::select('select * from apis where type=\'MT5\' and connectin=\'rest\' ; ')[0]->url; 
     
    
    
    

    if( count($orders)==0 && count($account_k)>0 ){
        $account = $account_k[0] ;
        //dd($account);
        if($account->type === "MT4"){
            $url = $rest_api_full . '/Connect?user='.$account->login.'&password='.$account->password.'&host='.$account->server.'&port='.$account->port.'';
             
             $token =Http::get( $url )->body()  ;
             $elementAdd = 0;
             
             if(strlen($token)==36){ 
                 $api_url = $rest_api_full. '/OrderHistory?id='  ;
                 $api_url = $api_url. $token ;

                 $response =Http::get( $api_url )->body()  ;
                 $arr = json_decode( $response );
                 $vat =  " <div>  NB = ".count($arr)." </div> " ;
                 //dd($arr) ; 
                 $l = 0;
                 $m = 0;
                if(count($arr)>=2){
                     foreach ($arr as $key => $v) {
                         $id = $v->ex->login. '::' .$v->ticket; 
                         if (Order::where('id', $id )->exists()) {
                             $ress =  ' Exist';
                             $l+=1;
                         }else{
                             $currentDateTime = new DateTime('now');
                             DB::insert('insert into orders (`id`, `login`, `token`, `ticket`, `closePrice`, `closeTime`, `comment`, `commission`, `lots`, `magicNumber`, `openPrice`, `openTime`, `placedType`, `profit`, `stopLoss`, `takeProfit`, `type`, `symbol`, `digits`, `swap`, `created_at`, `updated_at`) 
                             values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                             [$id, $v->ex->login, $token, $v->ticket, $v->closePrice, $v->closeTime, $v->comment, $v->commission, $v->lots, $v->magicNumber, $v->openPrice, $v->openTime, $v->placedType, $v->profit, $v->stopLoss, $v->takeProfit, $v->type, $v->symbol, $v->ex->digits,$v->swap, $currentDateTime, $currentDateTime]);
                             $elementAdd +=1;
                             $m+=1;
                         } 
                     } 
                 } 
                  
            } 
        }else{
            if($account->type === "MT5"){
                $url = $rest_api_full_MT5. '/Connect?user='.$account->login.'&password='.$account->password.'&host='.$account->server.'&port='.$account->port.''; 
                $token =Http::get( $url )->body()  ;
                $elementAdd = 0; 
                if(strlen($token)==36){ 
                    $api_url = $rest_api_full_MT5. '/OrderHistory?id='  ;
                    $api_url = $api_url. $token ;

                    $response = Http::get( $api_url )->body()  ;
                    $arr = json_decode( $response ); 
                    $MT5_orders = null;
                    $MT5_internalDeals = null;
                    $MT5_i = 0;
                    $l = 0;
                    $m = 0;

                    foreach ($arr as $key => $v) {
                        if($MT5_i==0){
                            $MT5_orders = $v ; 
                        }
                        if($MT5_i==1){
                            $MT5_internalDeals = $v ; 
                        }
                        $MT5_i=$MT5_i+1; 
                    } 
                    //dd($MT5_orders);
                     
                    if(count($MT5_orders)>=2){
                        foreach ($MT5_orders as $key => $v) {
                            $id = $account->login. '::' .$v->ticket; 
                            if (Order::where('id', $id )->exists()) {
                                $ress =  ' Exist';
                                $l+=1;
                            }else{
                                $currentDateTime = new DateTime('now');
                                DB::insert('insert into orders (`id`, `login`, `token`, `ticket`, `closePrice`, `closeTime`, `comment`, `commission`, `lots`, `magicNumber`, `openPrice`, `openTime`, `placedType`, `profit`, `stopLoss`, `takeProfit`, `type`, `symbol`, `digits`, `swap`, `created_at`, `updated_at`) 
                                values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                                [$id, $account->login, $token, $v->ticket, $v->closePrice, $v->closeTime, $v->comment, $v->commission, $v->lots, $v->expertId, $v->openPrice, $v->openTime, $v->placedType, $v->profit, $v->stopLoss, $v->takeProfit, $v->orderType, $v->symbol, $v->digits,$v->swap, $currentDateTime, $currentDateTime]);
                                $elementAdd +=1;
                                $m+=1;
                            } 
                        } 
                    } 

                }
               
            }

        }
           

        
        abort(404);
    }
     
    $NbTrades = 0 ;
    $LossTrades = 0;
    $WinTrades = 0;  
    $count = count($orders) ;
    if($count < 5){
        abort(404);
    }
    //dd($orders);

    
    //echo "<div>--------------------------------------------------------</div>"; 
    
    $TWRrate_FerstRate = 0 ;
    foreach ($orders as $key => $o) {
         
        $openPrice = $o->openPrice ;
        $closePrice = $o->closePrice ;
        $profit = (double)$o->profit + (double)$o->commission+ (double)$o->swap ;
        $Pips = 0;
        if( ($o->type=='Buy')||($o->type=='buy')||($o->type=='BUY') ){   $Pips = $closePrice - $openPrice ;  }else{  $Pips =  $openPrice - $closePrice ;  }
        $Pips = round(  $Pips*pow(10,$o->digits) ,2 )  ;
        if( $o->openPrice=="0" && $o->closePrice=="0"){
            
            if($TWRProfits!=0){ 
                $PR = ($Total_Profit_Withdrawal_Deposit)/($Total_Profit_Withdrawal_Deposit-$TWRProfits +0.001) -1  ;
                $TWRrate = $TWRrate*(1+$PR) ; 
               
                //echo "<div>Total All = " .$Total_Profit_Withdrawal_Deposit. " . . Profit = " .$TWRProfits. "   PR = " .$PR. "</div>"; 
                //echo "<div> we changet to div 2 -----------------------------------------------------------------------------------------<div>" ;

                $TWRProfits = 0;
                //[pl[opopjkpplpjoijoij]]
            }
            if($profit>0){
                $Deposit += $profit;
                if($FerstDeposit_OK==-1){
                    $FerstDeposit_OK = 1;
                    $FerstDeposit = $profit;
                }  
            }else{
                $Withdrawal+=$profit ;
            }
            $Total_Profit_Withdrawal_Deposit +=$profit;

        }else{
            $TWRProfits += $profit ; 
            $Total_Profit_Withdrawal_Deposit += $profit ; 
            $Total_Profit += $profit ; 
            $Total_pips += (double)$Pips;
            $NbTrades +=1;
            if($profit>0){
                $WinTrades +=1;
            }else{
                $LossTrades +=1;
            }
            $PR_Sub = ($Total_Profit_Withdrawal_Deposit)/($Total_Profit_Withdrawal_Deposit-$TWRProfits+0.001) -1  ;
            $TWRrate_FerstRate = $PR_Sub*100;
            
            $iTWRrate = $TWRrate*(1+$PR_Sub) ; 
            $iTWRratePersent = round( ($iTWRrate  - 1)*100 , 2);
            array_push( $iTWRratePersentTabel , [$iTWRratePersent , $o->closeTime] );
            //echo "<div>[PR_sub: ".round($PR_Sub,4)." , SubPresent=".round($TWRrate_FerstRate,2)."% , iTWRrate:{".round($iTWRrate,4)."} , iTWRratePersent: ".$iTWRratePersent."% ]</div>";

        }

        
    }
    if($TWRProfits!=0){
                
        $PR =   ($Total_Profit_Withdrawal_Deposit)/($Total_Profit_Withdrawal_Deposit-$TWRProfits+0.001) -1   ;
        $TWRrate = $TWRrate*(1+$PR) ;
        //echo "<div>Total All = " .$Total_Profit_Withdrawal_Deposit. " . . Profit = " .$TWRProfits. "   PR = " .$PR. "</div>";

        $TWRProfits = 0;
    }
    $TWRrate = round( ($TWRrate -1)*100 , 2) ;
    //echo "<div>-------------------------------------------------------- (" .$TWRrate. ") </div>"; 
    //dd($iTWRratePersentTabel);
    
    $ferst = strtotime ($orders[0]->openTime );
    $last = strtotime ($orders[$count-1]->openTime);

    //$ferst = substr($ferst,0, strpos($ferst,'T',0) );
    //$last = substr($last,0,strpos($last,'T',0));
    //$t1 = date('Y', strtotime($ferst)).date('m', strtotime($ferst)).date('d', strtotime($ferst));
    //$t1 =  intval(date('d', strtotime($ferst))) +  intval(date('m', strtotime($ferst)))*30 ;
    $dif_time = max($ferst,$last) - min($ferst,$last) ;
    $dif_time = $dif_time/60 ;
    $dif_time = $dif_time/60 ;
    $dif_time = $dif_time/24 ;
    $dif_time_days =intval( round( $dif_time , 0)) ; 
    $dif_time = $dif_time/30 ;
    $dif_time_Month =intval ( round( $dif_time , 0) ) ; 
    if($dif_time_days<=0) $dif_time_days = 1;
    if($dif_time_Month<=0) $dif_time_Month = 1;

    //dd( [$orders[0]->openTime,$orders[$count-1]->openTime,(max($ferst,$last) - min($ferst,$last)),$ferst,$last,$dif_time,$dif_time_days,$dif_time_Month] );

    //-------------------------------------------------
    // Drawdown Calculator
     
    $Total_Equity = 0;
    $Total_Min_Equity = 0;
    $Total_Min_Equity_Present = 0;  
    for ($i = 0; $i < $count; $i++) { 
        //$profit = (double)$o->profit + (double)$o->commission+ (double)$o->swap ; 
        $Total_Equity += (double)$orders[$i]->profit + (double)$orders[$i]->commission + (double)$orders[$i]->swap ; 
        $sub_Total_Equity = $Total_Equity;
        for ($j = $i+1; $j < $count; $j++) {
            if($orders[$j]->openPrice=="0") {
                break;
            }
            
            $sub_Total_Equity  += (double)$orders[$j]->profit ;
            
            
            if( ($sub_Total_Equity-$Total_Equity) < $Total_Min_Equity ){
                $Total_Min_Equity = $sub_Total_Equity-$Total_Equity ;
                $Total_Min_Equity_Present = round( $Total_Min_Equity*100/$Total_Equity , 2 ) ;
                //echo "<br>  val : " .$Total_Min_Equity;
                //echo "<br> Total_Equity= " .$Total_Equity. " sub_Total_Equity = " .$sub_Total_Equity;
            }
        }
        if($Total_Equity>$HighestDeposit){
            $HighestDeposit = $Total_Equity ;
            $HighestDeposi_date = date('D', strtotime($orders[$i]->openTime) ) . " of " . date('M', strtotime($orders[$i]->openTime) ).
                                    " " .date('H', strtotime($orders[$i]->openTime) ).":" .date('i', strtotime($orders[$i]->openTime) )  ;
        }
    } 
    //echo "<br> DRW " . $Total_Min_Equity;
?>
<!-- Calculate Pips $PipsPIPContentTabel = [] ;  -->
<?php
    $PipsPIPContentTabel = [] ; 
    $PipsPIPContentTabelEachOneSeparately = [] ; 
    $ProfitContentTabelEachOneSeparately = [];
    $totalPips = 0;
    $TotalCommintion = 0;
    $TotalLotsSize = 0;
    foreach ($orders as $key => $o) {
        $openPrice = $o->openPrice ;
        $closePrice = $o->closePrice ;
        $profit = (double)$o->profit ;
        $symbol = strtolower($o->symbol);
        $digits = $o->digits ;
        $Pips = 0;
        foreach ($symbolpips as $key => $sPips){  
            if(str_contains($symbol, $sPips[0])){
                $digits = $sPips[1] ;
                break;
            }
        }
        //----------------------------------------------------------------
        if( $o->openPrice=="0" && $o->closePrice=="0"){
            $u = 8;
        }else{
            if( ($o->type=='Buy')||($o->type=='buy')||($o->type=='BUY') )
            {
                $Pips = $closePrice - $openPrice ;  
            }else{  
                $Pips =  $openPrice - $closePrice ;  
            }
            //------------------------------------
            $Pips = round(  $Pips*pow(10,$digits) ,2 )  ;
            
            //echo "<br>[".$digits.":" .$symbol."]:[".$openPrice.", ".$closePrice."]={".$Pips." pips}";
            
            $totalPips = $totalPips + $Pips ;
            array_push( $PipsPIPContentTabel , [$totalPips , $o->closeTime] );
            array_push( $PipsPIPContentTabelEachOneSeparately,[$Pips , $o->closeTime] );
            $profit = (double)$o->profit + (double)$o->commission + (double)$o->swap ; 
            $TotalCommintion +=(double)$o->commission + (double)$o->swap ;  
            $TotalLotsSize +=$o->lots ;
            array_push( $ProfitContentTabelEachOneSeparately ,[$profit , $o->closeTime] );
        }
        
    }
    //dd($symbolpips);
?>

<!-- Calculate  $DRAWDOWN  = [] ;  -->
<?php
    
    //dd($orders);
    $orders = DB::select('select * from orders where login = :login ORDER BY closeTime ' , ['login' => $id]);
    $oDate = "2000-01-01";
    $days = [] ;
    $subPrices = [] ;
    $Prices = [] ;
    $totalBalances = 0;
    $totalProfits = 0;
    //$profit = (double)$o->profit + (double)$o->commission+ (double)$o->swap ; 
    foreach ( $orders as $key => $o ) {
         $closeTime = $o->closeTime;
         $date = substr($closeTime,0, strpos($closeTime,'T',0) );
         if($oDate == "2000-01-01"){  
            $oDate = $date ;
            array_push($days,$date);
         } 
         $totalBalances +=(double)$o->profit + (double)$o->commission+ (double)$o->swap ;
         if($o->openPrice!=0){
            $totalProfits +=(double)$o->profit + (double)$o->commission+ (double)$o->swap ;
         } 
         if($oDate != $date){
            $oDate = $date;
            array_push($days,$date);
            array_push( $Prices , $subPrices  );
            $subPrices = [] ;
         } 
         
        array_push( $subPrices , [ $date , (double)$o->profit + (double)$o->commission+ (double)$o->swap ,$totalBalances ,$o->openPrice   ] );
          
         
         

         //echo "<br>" . $date;
    } 

    array_push( $Prices , $subPrices ); 
    $MaxProfit = 0;
    $MaxDDDayly = [] ;
    //dd($Prices);
    foreach ( $Prices as $key => $p ) {
        $StartBalance = $p[0][2] ;
        $MaxStartBalance = $p[0][2] ;
        $Drow = 0;
        $profit_ = 0;
        //echo "<br> ------------------>  Date: " .$p[0][0]. " StartBalance = " .$StartBalance  ;
        foreach ( $p as $key => $ip ) {
            
            if($ip[3]==0){
               //
            }else{
                $profit_ += $ip[1] ;
                $NewBalance = $StartBalance + $profit_ ;
                $Dif = ($MaxStartBalance - $NewBalance ) ;
                if( $Dif>0 ){
                    if($Dif > $Drow){
                        $Drow = $Dif ;
                    }
                    
                }else{
                    $MaxStartBalance = $NewBalance;
                }
                //echo "<br> Profit: " .$profit_. " NewBalance: " .$NewBalance. " MaxB : " .$MaxStartBalance. " Drow = " .round($Drow,2);
                
            }
            
        } 
        $PrsentDD = round( $Drow*100/$MaxStartBalance , 2 ) ;
        array_push( $MaxDDDayly , [ $p[0][0] , $PrsentDD ] );
        //echo "<br> ----------------------------------------------------------------------------------------> " . $PrsentDD   . "%";
         
    } 

    //dd($MaxDDDayly);
  
?>

<!-- Calculate  $Symbols  = [] ;  -->
<?php
    
    $Symbols = []; // ['EURUSD',212] = ['Symbol','NbTrades'] ;
    //dd($orders);
    foreach ( $orders as $key => $o ) {
          $sym = $o->symbol ;
          if( $o->openPrice!=0 ){
                $yes = -1;
                $index = 0;
                foreach ( $Symbols as $key => $s ) {
                    //dd($s);
                    if($s[0]==$sym){  $yes=$index; }else{ $index +=1; }
                }
                if($yes==-1){
                    array_push($Symbols,[$sym,1]);
                    //echo "<br> comment : " .$o->symbol;
                }else{
                    $Symbols[$yes][1] +=1;
                }

                

            }
        

        
    } 

    //dd($Symbols);
?>


<!--  //echo $test_str; -->
<?php 
    $Total_Profit_Present_Abs  = 0;//
    try {
        $Total_Profit_Present_Abs = round(  ($Total_Profit*100)/($Deposit) , 2) ;
    } catch (DivisionByZeroError $e) {  }

    $Total_Profit_Present  = 0;//
    try {
        $Total_Profit_Present = round(  ($Total_Profit*100)/($FerstDeposit) , 2) ;
    } catch (DivisionByZeroError $e) {  }

    $Total_Profit_str = $Total_Profit. "$" ;
    if($Total_Profit>0){
    $Total_Profit_str = "+" .$Total_Profit. "$" ;
    }
    $Total_Profit_Present_Abs_str = $Total_Profit_Present_Abs. "%" ;
    if($Total_Profit_Present_Abs>0){
    $Total_Profit_Present_Abs_str = "+" .$Total_Profit_Present_Abs_str ;
    } 

    $TWRrate_str = $TWRrate. "%" ;
    if($TWRrate>0){
    $TWRrate_str = "+" .$TWRrate_str ;
    } 
    $Daily = round($TWRrate/$dif_time_days,2) ;
    $Monthly = round($TWRrate/$dif_time_Month,2) ;

    $test_str = "
    <div> profit :" .$Total_Profit_str. "  </div>
    <div> profit Abs Gain :" .$Total_Profit_Present_Abs_str. "  </div>
    <div> profit TWR Gain:" .$TWRrate_str. "  </div>
    <div> Daily:+" .$Daily. "%  </div>
    <div> Monthly:+" .$Monthly. "%  </div>
    <div> Drawdown:" .$Total_Min_Equity. "$  </div> 
    <div> Drawdown Present:" .$Total_Min_Equity_Present. "%   </div>

                    ";


    //echo $test_str;
    DB::table('strategies')->where('login', $id)->update(['profit_usd' => $Total_Profit ,'profit_prc' => $TWRrate]);  
?>

<!-- $accountBalance = $res->accountBalance; $accountEquity = $res->accountEquity; --> 
<?php 

        $accountBalance = 0;
        $accountEquity = 0;
        $accountType = 0;
        $accountCompanyName = "FBS";
        $accountLeverage = 100;

    
    $account = DB::select('select * from strategies where login = :login ' , ['login' => $id]);
    if( count($account)>0   ){
           $account = $account[0];
           $res=null; 
            if($account->type == "MT4"){ 
                $url = $rest_api_full. '/Connect?user='.$account->login.'&password='.$account->password.'&host='.$account->server.'&port='.$account->port.'';
                try {    
                     $token =Http::get( $url )->body()  ; 
                    } catch (Exception $e) {  } 
                $url2 = $rest_api_full. '/QuoteClient?id='.$token.'';
                
                try {    
                    $res = json_decode ( Http::get( $url2 )->body() )  ;
                    } catch (Exception $e) {  } 
                //dd($res);
                if($res!=null){
                    $accountBalance = $res->accountBalance;
                    $accountEquity = $res->accountEquity;
                    $accountType = $res->accountType ;
                    $accountCompanyName = $res->accountCompanyName;
                    $accountLeverage = $res->accountLeverage; 
                    if($accountType=="Contest"){
                        $accountType = "Demo";
                    }
                }else { 
                    $accountBalance = round( $Deposit + $Total_Profit +$Withdrawal ,2) ;
                    $accountEquity = round( $Deposit + $Total_Profit +$Withdrawal ,2) ;
                    $accountType = "Real";
                }
                 
            }else{
                //$url = $rest_api_full_MT5. '/Connect?user='.$account->login.'&password='.$account->password.'&host='.$account->server.'&port='.$account->port.''; 
                //$token =Http::get( $url )->body()  ;
                if($account->type == "MT5"){ 
                    $url = $rest_api_full_MT5. '/Connect?user='.$account->login.'&password='.$account->password.'&host='.$account->server.'&port='.$account->port.'';
                    try {    
                        $token =Http::get( $url )->body()  ; 
                        } catch (Exception $e) {  } 
                    $url2 = $rest_api_full_MT5. '/QuoteClient?id='.$token.'';
                    
                    try {    
                        $res = json_decode ( Http::get( $url2 )->body() )  ;
                        } catch (Exception $e) {  } 
                    //dd($res);
                    if($res!=null){
                        $accountBalance = $res->accountBalance;
                        $accountEquity = $res->accountEquity;
                        $accountType = $res->accountType ;
                        $accountCompanyName = $res->accountCompanyName;
                        $accountLeverage = $res->accountLeverage; 
                        if($accountType=="Contest"){
                            $accountType = "Demo";
                        }
                    }else { 
                        $accountBalance = round( $Deposit + $Total_Profit +$Withdrawal ,2) ;
                        $accountEquity = round( $Deposit + $Total_Profit +$Withdrawal ,2) ;
                        $accountType = "Real";
                    }  
                }
            }
    }
   
    
?>

<!-- Collecting data  -->

<!-- select * from orders where login = :login ORDER BY closeTime -->
<?php
    
    $orders = DB::select('select * from orders where login = :login ORDER BY closeTime   ' , ['login' => $id]);
    $ordersGainPercent = $orders;
    //dd($ordersGainPercent);
    $orders_ = [];
    /*
    $overview =  DB::select('select * from overviews where login = :login ' , ['login' => $id]); 
    if(count($overview)>=1){
        $overview = $overview[0];
        
        $end = count($orders) ;
        $start = 0;
        if( ($overview->GainPercentStartIndex != null) && ($overview->GainPercentStartIndex>0)  )
            $start = $overview->GainPercentStartIndex;
        if(  ($overview->GainPercentEndIndex != null) && ($overview->GainPercentEndIndex>0)  )
            $end = $overview->GainPercentEndIndex ;
        
        
        $ordersGainPercent = array_slice($orders, $start , ($end-$start)  ) ;
         
            

         
        
    }
    */
    
?>
<script>
    var DepositsAll = [];
    var Balance = [];
    var xc = 9;
    var iTWRratePersentTabel = [];
    var PipsPIPContentTabel =[];
    var MaxDDDayly = [] ;
    var Symbols = [];
    var HourlyTabel = [];var xHourlyTabel = [];var yHourlyTabel = [];var zHourlyTabel = [];
    var DailyTabel = [];var xDailyTabel = [];var yDailyTabel = [];var zDailyTabel = [];
    var AvgTradeLengthTabelProfit = [];

 
</script>
@foreach ($ordersGainPercent as $key => $o)
<script> 
Balance.push( [      {{ round( (double)$o->profit + (double)$o->commission+ (double)$o->swap , 2) }}  ,  {{   (double)$o->openPrice  }}  ,'  {{ $o->closeTime }} '     ] ); 

//closeTime 
if({{ $o->closePrice }}==0){
    console.log( " Profit : "+{{ (double)$o->profit }} );
    DepositsAll.push[ '{{ $o->closeTime }}'  , {{ (double)$o->profit }}];
}
</script>
@endforeach

@foreach ($iTWRratePersentTabel as $key => $iTWRrate)
<script> iTWRratePersentTabel.push( [      {{ round( (double)$iTWRrate[0] , 2) }}   ,'  {{ $iTWRrate[1] }} '     ] ); </script>
@endforeach

@foreach ($PipsPIPContentTabel as $key => $iPips)
<script> PipsPIPContentTabel.push( [      {{ round( (double)$iPips[0] , 2) }}   ,'  {{ $iTWRrate[1] }} '     ] ); </script>
@endforeach

@foreach ($MaxDDDayly as $key => $iDD)
<script> MaxDDDayly.push( [    '{{$iDD[0] }}'   ,  {{ $iDD[1] }}      ] ); </script>
@endforeach

@foreach ($Symbols as $key => $s)
<script> Symbols.push( [    '{{$s[0] }}'   ,  {{ $s[1] }}      ] ); </script>
@endforeach





<!-- Profits = []; TimeProfits = [] ; TWRrate = []; TimeTWRrate = []; Pips = []; TimePips = []; -->   
<script> 
     
    Profits = [];
    Profits_for_Monthly = [];
    TimeProfits = [] ;
    //-------------
    TWRrate = [];
    TimeTWRrate = [];
    //-------------
    Pips = [];
    TimePips = [];
    //-------------
    AllProfits = 0; 
    AllProfits2 = 0;
    GainPercentContent = [] ;
    //------------
    //AdvancedStats = [[1628775000000,-72282600],[1628861400000,-59375000],[1629120600000,-103296000],[1629207000000,92229700],[1629293400000,86326000],[1629379800000,86960300],[1629466200000,60549600],[1629725400000,60131800],[1629811800000,48606400],[1629898200000,58991300],[1629984600000,48597200],[1630071000000,55802400],[1630330200000,90956700],[1630416600000,86453100],[1630503000000,80313700],[1630589400000,71115500],[1630675800000,57808700],[1631021400000,82278300],[1631107800000,74420200],[1631194200000,57305700],[1631280600000,140893200],[1631539800000,102404300],[1631626200000,109296300],[1631712600000,83281300],[1631799000000,68034100],[1631885400000,129868800],[1632144600000,123478900],[1632231000000,75834000],[1632317400000,76404300],[1632403800000,64838200],[1632490200000,53477900],[1632749400000,74150700],[1632835800000,108972300],[1632922200000,74602000],[1633008600000,89056700],[1633095000000,94639600],[1633354200000,98322000],[1633440600000,80861100],[1633527000000,83221100],[1633613400000,61732700],[1633699800000,58773200],[1633959000000,64452200],[1634045400000,73035900],[1634131800000,78762700],[1634218200000,69907100],[1634304600000,67940300],[1634563800000,85589200],[1634650200000,76378900],[1634736600000,58418800],[1634823000000,61421000],[1634909400000,58883400],[1635168600000,50720600],[1635255000000,60893400],[1635341400000,56094900],[1635427800000,100077900],[1635514200000,124953200],[1635773400000,74588300],[1635859800000,69122000],[1635946200000,54511500],[1636032600000,60394600],[1636119000000,65463900],[1636381800000,55020900],[1636468200000,56787900],[1636554600000,65187100],[1636641000000,41000000],[1636727400000,63804000],[1636986600000,59222800],[1637073000000,59256200],[1637159400000,88807000],[1637245800000,137827700],[1637332200000,117305600],[1637591400000,117467900],[1637677800000,96041900],[1637764200000,69463600],[1637937000000,76959800],[1638196200000,88748200],[1638282600000,174048100],[1638369000000,152052500],[1638455400000,136739200],[1638541800000,118023100],[1638801000000,107497000],[1638887400000,120405400],[1638973800000,116998900],[1639060200000,108923700],[1639146600000,115402700],[1639405800000,153237000],[1639492200000,139380400],[1639578600000,131063300],[1639665000000,150185800],[1639751400000,195432700],[1640010600000,107499100],[1640097000000,91185900],[1640183400000,92135300],[1640269800000,68356600],[1640615400000,74919600],[1640701800000,79144300],[1640788200000,62348900],[1640874600000,59773000],[1640961000000,64062300],[1641220200000,104487900],[1641306600000,99310400],[1641393000000,94537600],[1641479400000,96904000],[1641565800000,86709100],[1641825000000,106765600],[1641911400000,76138300],[1641997800000,74805200],[1642084200000,84505800],[1642170600000,80440800],[1642516200000,90956700],[1642602600000,94815000],[1642689000000,91420500],[1642775400000,122848900],[1643034600000,162294600],[1643121000000,115798400],[1643207400000,108275300],[1643293800000,121954600],[1643380200000,179935700],[1643639400000,115541600],[1643725800000,86213900],[1643812200000,84914300],[1643898600000,89418100],[1643985000000,82465400],[1644244200000,77251200],[1644330600000,74829200],[1644417000000,71285000],[1644503400000,90865900],[1644589800000,98670700],[1644849000000,86185500],[1644935400000,62527400],[1645021800000,61177400],[1645108200000,69589300],[1645194600000,82772700],[1645540200000,91162800],[1645626600000,90009200],[1645713000000,141147500],[1645799400000,91974200],[1646058600000,95056600],[1646145000000,83474400],[1646231400000,79724800],[1646317800000,76678400],[1646404200000,83737200],[1646663400000,96418800],[1646749800000,131148300],[1646836200000,91454900],[1646922600000,105342000],[1647009000000,96970100],[1647264600000,108732100],[1647351000000,92964300],[1647437400000,102300200],[1647523800000,75615400],[1647610200000,123511700],[1647869400000,95811400],[1647955800000,81532000],[1648042200000,98062700] ] ;
    AdvancedStats = [];
    //------------
    old_GainPercentContent = Balance[0][0];
    GainPercentContentAll = 0;
    Allroi_prs = 0;
    var allDeposits = 0;
    var allWithdrawals = 0;
    // Balance['profit','openPrice','openTime']
    //console.log("Balance");
    //console.log(Balance);
    // HFTHaidra

    Balance.forEach(( balance, index ) => {
        Profit = Math.round( balance[0] * 100)/100   ; 
        AllProfits +=Profit ;
        let time = balance[2];
        time = time.replace(" ",""); time = time.replace(" ",""); time = time.replace(" ",""); time = time.replace(" ",""); time = time.replace("T"," ");
        if(balance[1]==0){
            if(balance[0]>0){
                allDeposits += balance[0];
            }else{
                allWithdrawals += balance[0];
            }
        }
        if(balance[1]!=0){
            AllProfits2+=Profit ;
            Profits.push( [  time    ,  Math.round(AllProfits2*100)/100   ] ); 
            Profits_for_Monthly.push( [  time    ,  Math.round(AllProfits2*100)/100  ,allDeposits,allWithdrawals ] ); 
            TimeProfits.push(   time   );
        } 
        
        
        Withdrawal_Deposit = balance[1];
        if(Withdrawal_Deposit == 0){
            //console.log(Profit + " . "+Withdrawal_Deposit);
        }  
        // GainPercentContentCalculation
        
        roi_prs = (  (AllProfits)/(old_GainPercentContent) -1 )*100 ;
        Allroi_prs += roi_prs;
        old_GainPercentContent = AllProfits;
        //GainPercentContent.push( [ time , Math.round( Allroi_prs*100    )/100 ] );
        GainPercentContent.push( [ index , Math.round( Allroi_prs*100    )/100 ] ); 
        GainPercentContentAll+=roi_prs;
    });  
    iTWRratePersentTabel.forEach(( iTWRrate, index ) => {
        let time = iTWRrate[1];
        time = time.replace(" ",""); time = time.replace(" ",""); time = time.replace(" ",""); time = time.replace(" ",""); time = time.replace("T"," "); 
        //time = Date.parse(time);
        //TWRrate.push([time,iTWRrate[0]]);
        TWRrate.push([index,iTWRrate[0]]);
        TimeTWRrate.push(time);
    });
    PipsPIPContentTabel.forEach(( iPips, index ) => {
        let time = iPips[1];
        time = time.replace(" ",""); time = time.replace(" ",""); time = time.replace(" ",""); time = time.replace(" ",""); time = time.replace("T"," "); 
        //time = Date.parse(time);
        Pips.push([index,iPips[0]]);
        TimePips.push(time);
    });
    //Symbols Calculation.........
    AllSymbols = [] ;
    TotalTrades  =0;
    Symbols.forEach(( iSym, index ) => {
        TotalTrades+=iSym[1];
    });
    Symbols.forEach(( iSym, index ) => {
        //Math.round( Allroi_prs*100    )/100 
        AllSymbols.push([iSym[0],   Math.round(  ( (iSym[1]*100)/TotalTrades ) *100  )/100   ]);
    });  
    MaxProfitParDay = [];
    oDay = Profits[0][0].substring(0, 10);
    subProfit = 0;
     
    Profits.forEach(( Pro, index ) => {
        if(index>0){
            day = Pro[0].substring(0, 10);
            profit_ =  (Pro[1] - Profits[index-1][1])  ;
            if(oDay==day){
                subProfit+=profit_ ;
            }else{
                MaxProfitParDay.push( [ oDay  , Math.round( subProfit*100 )/100  ] );
                oDay = day ;
                subProfit = profit_;
            }
            
        }
        
    });
    MaxProfitParDay.push( [ oDay  , Math.round( subProfit*100 )/100   ] );
    //console.log(" Day: "+oDay+" is Done...");
    oDay = day ;
    subProfit = 0; 
    AdvancedStats = MaxProfitParDay ;
    //console.log(Profits); 
    //console.log(MaxProfitParDay); 
    //console.log(subProfit);
    
    
</script>
















































<!--  Change Details[TWR. Gain :,Abs. Gain: , Daily,.....] -->   
<?php 
    $Details = DB::select('select * from details where login = :login ' , ['login' => $id]);
    

    $Interest = round( $Total_Profit/$dif_time_days ,2 ) ;
    $updated_Time_last = str_replace ("T"," ", $orders[$count-1]->openTime ) ;
    $Ddescription = "No description entered."; 
    $Technical = "Technical";
    $Manual = "Manual";
    $Sstarted = str_replace ("T"," ", $orders[$count-1]->openTime ) ;
    $timezones = "GMT +3";
    if(count($Details)==1){
        $Detail = $Details[0] ;
        if($Detail->twr_gain!=0){
            $TWRrate_str = $Detail->twr_gain ;
        }

        if($Detail->abs_gain!=0){
            $Total_Profit_Present_Abs_str = $Detail->abs_gain ;
        }

        if($Detail->daily!=0){
            $Daily = $Detail->daily ;
        }

        if($Detail->monthly!=0){
            $Monthly = $Detail->monthly ;
        }

        if($Detail->drawdown!=0){
            $Total_Min_Equity_Present =$Detail->drawdown ;
        }

        if($Detail->drawdown_usd!=0){
            $Total_Min_Equity = $Detail->drawdown_usd ;
        }

        if($Detail->balance!=0){
            $accountBalance = $Detail->balance ;
        }

        if($Detail->equity!=0){
            $accountEquity = $Detail->equity ;
        }

        if($Detail->highest_date!=0){
            $HighestDeposi_date = $Detail->highest_date ;
        }

        if($Detail->highest!=0){
            $HighestDeposit = $Detail->highest ;
        }

        if($Detail->profit!=0){
            $Total_Profit = $Detail->profit ;
        } 
        
        if($Detail->interest!=0){
            $Interest = $Detail->interest ;
        }

        if($Detail->deposits!=0){
            $Deposit = $Detail->deposits ;
        }

        if($Detail->withdrawals!=0){
            $Withdrawal = $Detail->withdrawals ;
        }

        if($Detail->updated!=0){
            $updated_Time_last = $Detail->updated ;
        }

        if($Detail->description!=0){
            $Ddescription = $Detail->description ;
        }

        if($Detail->trades!=0){
            $NbTrades = $Detail->trades ;
        }
        
        if($Detail->broker!=0){
            $accountCompanyName = $Detail->broker ;
        }

        
        if($Detail->leverage!=0){
            $accountLeverage = $Detail->leverage ;
        }
        
        if($Detail->type!=0){
            $accountType = $Detail->type ;
        }
        
        
        if($Detail->system!=0){
            $Technical = $Detail->system ;
        }
        
        if($Detail->trading!=0){
            $Manual = $Detail->trading ;
        }
        
        if($Detail->started!=0){
            $Sstarted = $Detail->started ;
        }
        
        if($Detail->timezone!=0){
            $timezones = $Detail->timezone ;
        }

        if($Interest>0){
            $Interest ="+" .$Interest;
        }
        if($Total_Profit>0){
            $Total_Profit = "+" .$Total_Profit ;
        }
        if($Daily>0){
            $Daily = "+" .$Daily ; 
        }
        if($Monthly>0){
            $Monthly = "+" .$Monthly;
        }
        
        if($Deposit>0){
            $Deposit = "+" .$Deposit;
        }
        if($Withdrawal>0){
            $Withdrawal = "+" .$Withdrawal;
        }

        //dd($Detail );
    }

?>











<style>
    .customAnalyze{
        width: 90%;
        height: 550px; 
    }
    .infoContainerDiv{ 
        
    }
    .chartContainerDiv{ 
        height: 550px;
    }
    .infoPortlet{
        width: 300px;
        height: 530px;
        margin: 5px 5px 5px 5px;
        border: 1px solid rgb(120, 153, 214);
    }
    .chartContainerSubDiv{
        
        height: 530px;
        margin: 5px 5px 5px 5px;
        border: 1px solid rgb(120, 153, 214);
    }
    @media screen and (max-width: 140px) {
        .customAnalyze{
            display: block;
        }
        .customAnalyze{ 
            height: 1100px; 
            }
        .advancedStatisticsContainerDiv .rowHaidarSub{
            
        }
       .advancedStatisticsContainerDiv .colHaidra{
         
       }
    }
    .infoPortlet .Stats , .infoPortlet .Info {
        visibility: hidden;
        width: 298px;
        padding: 10px 0%;
        font-size: 12px; 
    }
    .infoPortlet .Stats.active , .infoPortlet .Info.active {
        visibility: initial; 
    }



    .infoPortlet .nav-content{
        position: absolute;
    }
    .infoPortlet .nav-content hr{
        margin: 2px 0px;
    }
    .infoPortlet .nav-content tbody , .infoPortlet .nav-content table{
        width: 100%;
    }
    .infoPortlet .nav-content tr{
        width: 100% ; 
        padding-top:20px; 
    }
    .infoPortlet .nav-content td{
        padding-left: 10px;
        padding-right: 10px;
    }
    .infoPortlet .nav-content tr:hover{
        background-color: rgba(169, 169, 169, 0.322) ;
    }
     
    td span .green{
        color: #00aa38;
    }
</style>

<style>
    #container,.highcharts-data-table table {
        min-width: 350px;
        max-width: 800px;
        margin: 1em auto;
     }

        #container {
            height: 410px;
        }

        .highcharts-data-table table {
            font-family: Verdana, sans-serif;
            border-collapse: collapse;
            border: 1px solid #ebebeb;
            margin: 10px auto;
            text-align: center;
            width: 100%;
            max-width: 500px;
        }

        .highcharts-data-table caption {
            padding: 1em 0;
            font-size: 1.2em;
            color: #555;
        }

        .highcharts-data-table th {
            font-weight: 600;
            padding: 0.5em;
        }

        .highcharts-data-table td,
        .highcharts-data-table th,
        .highcharts-data-table caption {
            padding: 0.5em;
        }

        .highcharts-data-table thead tr,
        .highcharts-data-table tr:nth-child(even) {
            background: #f8f8f8;
        }

        .highcharts-data-table tr:hover {
            background: #f1f7ff;
        }
         

</style>
<div class=" flex justify-center pt-2 ">
    <div class="customAnalyze id_{{$id}} flex      ">

        <!-- ... -->
        <!-- ... -->
        <!-- ... -->
        <!-- ... -->
        <!-- ... --> 
        <!--  infoContainerDiv ------------------------------------------------------------------------------------- -->
        <div class="infoContainerDiv  w-auto flex justify-center items-center ">
            <div class="infoPortlet   ">
                    
                <ul class="nav nav-tabs pt-2">
                    <li class="nav-item" >
                        <a class="nav-link disabled" aria-disabled="true" targetcontent="Details" >Details</a>
                    </li>
                    <li class="nav-item" >
                      <a class="nav-link active" aria-current="page" href="#CopyTrade" targetcontent="Stats" >Stats</a>
                    </li>
                    <li class="nav-item" >
                      <a class="nav-link" href="#CopyTrade" targetcontent="Info" >Info</a>
                    </li> 
                    
                </ul>
                <div >
                    <div class="  ">
                        <div class="Details nav-content " style="width: 0; height: 0;"></div>
                        <div class="Stats nav-content active ">
                            <div class="table-scrollable-borderless" style="font-size: 12px;">
                                <table class="table-hover table-small">
                                    <tbody class="  ">
                                        <tr>
                                            <td><label class="custom-analysis-popover" data-title="Gain" data-content="Time-Weighted Return (TWR) that measures the performance of a dollar invested in the system since inception.<br/>TWR measurement is required by the Global Investment Performance Standards published by the CFA Institute. Its distinguishing characteristic is that cash inflows, cash outflows and amounts invested over different time periods have no impact on the return.">
                                            <span><b class="dotted">TWR. Gain :</b></span>
                                            </label></td>
                                            <td class=" flex justify-end"><span><b><span class="green">{{$TWRrate_str}}</span></b></span>
                                            </td>
                                        </tr>
                                        <tr>
                                        <td><label class="custom-analysis-popover" data-title="Absolute Gain" data-content="Return of the investment as a percentage of the total deposits.<br/>By definition, new deposits will affect the absolute gain.">
                                            <span class="dotted">
                                            Abs. Gain:
                                            </span>
                                            </label></td>
                                        <td class=" flex justify-end"><span><span class="green">{{$Total_Profit_Present_Abs_str}}</span></span></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <hr>
                                <table class="table-hover table-small">
                                    <tbody>
                                        <tr>
                                            <td><label class="custom-analysis-popover" data-title="Daily Gain" data-content="Daily compound rate of return leading to the total gain.">
                                            <span class="dotted">
                                            Daily
                                            </span>
                                            </label></td>
                                            <td class=" flex justify-end"><span>{{$Daily}}%</span></td>
                                        </tr>
                                        <tr>
                                            <td><label class="custom-analysis-popover" data-title="Monthly Gain" data-content="Monthly compound rate of return leading to the total gain.">
                                            <span class="dotted">
                                            Monthly:
                                            </span>
                                            </label></td>
                                            <td class=" flex justify-end"><span>{{$Monthly}}%</span></td>
                                        </tr>
                                        <tr>
                                            <td> Drawdown:</td>
                                            <td class=" flex justify-end"><span>{{$Total_Min_Equity_Present}}% </span></td>
                                        </tr>
                                        <tr>
                                            <td> Drawdown USD:</td>
                                            <td class=" flex justify-end"><span> {{round($Total_Min_Equity,2)}}$ </span></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <hr>
                                <table class="table-hover table-small">
                                    <tbody>
                                        <tr>
                                            <td> Balance:</td>
                                            <td class=" flex justify-end"><span>
                                            <span id="statsBalance">{{$accountBalance}}$</span>
                                            </span></td>
                                        </tr>
                                        <tr>
                                            <td> Equity:</td>
                                            <td class=" flex justify-end">
                                            <span>
                                            <span class="font11">
                                             
                                            </span>
                                            <span id="statsEquity">{{$accountEquity}}$</span>


                                            

                                            </span>
                                            </td>
                                            </tr>
                                        <tr>
                                            <td> Highest:</td>
                                            <td class=" flex justify-end">
                                            <span>
                                            <span class="gray font11">
                                            [{{$HighestDeposi_date}}]
                                            </span>
                                            <span>
                                            {{$HighestDeposit}}$
                                            </span>
                                            </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td> Profit:</td>
                                            <td class=" flex justify-end">
                                            <span>
                                            <span class="green">{{$Total_Profit}}$</span>
                                            </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td> Interest:</td>
                                            <td class=" flex justify-end">
                                            <span>
                                            <span> {{$Interest}}$</span>
                                            </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <hr>
                                <table class="table-hover table-small">
                                    <tbody>
                                        <tr>
                                            <td> Deposits:</td>
                                            <td class=" flex justify-end">
                                            <span> {{$Deposit}}$
                                            </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td> Withdrawals:</td>
                                            <td class=" flex justify-end">
                                            <span>{{$Withdrawal}}$
                                            </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <hr>
                                <table class="table-hover table-small">
                                    <tbody>
                                        <tr>
                                            <td>Updated</td>
                                            <td class=" flex justify-end">
                                            <span class="floatNone" id="lastUpdatedTime" time="">
                                                {{ $updated_Time_last }}
                                            </span>
                                            </td>
                                        </tr> 
                                    </tbody>
                                </table>
                                </div>
                        </div> 

                       <div class="Info nav-content">
                            <div>
                                <h3 class=" text-sm px-2">Description:</h3>
                                <p class=" px-4 ">{{$Ddescription}}</p>
                            </div>
                            <hr>
                            <table class="table-hover table-small "  style="font-size: 12px;" >
                                <tbody>
                                    <tr>
                                        <td>Trades:</td>
                                        <td  class=" flex justify-end" ><span>{{$NbTrades}}</span></td> 
                                    </tr>
                                    <tr>
                                        <td> Broker:</td>
                                        <td  class=" flex justify-end" ><span>{{  $accountCompanyName }}</span></td> 
                                    </tr>
                                <tr>
                                    <td> Leverage:</td>
                                    <td  class=" flex justify-end"  ><span>        1:{{$accountLeverage}}    </span></td> 
                                </tr>
                                <tr>
                                    <td> Type:</td>
                                    <td  class=" flex justify-end" ><span>            {{$accountType}}    </span></td>
                        
                                </tr>
                                <tr>
                                    <td> System:</td>
                                    <td class=" flex justify-end" ><span>      {{$Technical}}      </span></td> 
                                </tr>
                                <tr>
                                    <td> Trading:</td>
                                    <td  class=" flex justify-end" ><span>            {{$Manual}}    </span></td>
                        
                                </tr>
                                <tr>
                                    <td> Started:</td>
                                    <td  class=" flex justify-end" ><span>{{ $Sstarted }}</span>
                                    </td>
                        
                                </tr>
                                 
                                <tr>
                                    <td> Timezone:</td>
                                    <td  class=" flex justify-end" ><span> {{$timezones}} </span></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        
                    </div>
                </div>

            </div>
        </div>

        <!-- ... -->
        <!-- ... -->
        <!-- ... -->
        <!-- ... -->
        <!-- ... -->
        <!-- style chartContainerDiv ------------------------------------------------------------------------------------- -->
        <style>
                .chartContainerSubDiv .Gain, .chartContainerSubDiv .Symbols , .chartContainerSubDiv .AdvancedStats ,.chartContainerSubDiv .Monthly {
                    visibility: hidden;  
                    font-size: 12px;  
                }
                .chartContainerSubDiv .nav-content.active {
                    visibility: initial; 
                     
                } 
                .chartContainerSubDiv .nav-content{
                    position:absolute;   
                    border: 2px solid #00aa3900;
                }
                .HaidraTabs304 ,.HaidraTabs305{
                    display:flex; 
                    padding-left: 10px;
                    padding-top:4px;
                }
                 
                .HaidraTab, .HaidraTab5{ 
                    height: 30px;
                    padding: 5px;
                    background-color: #f9f9f9;
                    color:#939393 ;
                    border-left: 1px solid rgb(255, 255, 255);
                    font-size: 0.875rem;
                    font-weight: 600;
                    display:flex;
                    align-items: center;
                    justify-content: center;
                    cursor: pointer;

                }
                .HaidraTab:hover , .HaidraTab5:hover {  color: rgb(0, 0, 0);   }
                .HaidraTab.active , .HaidraTab5.active{
                    font-weight: 700;  
                    background-color: #ffe3cc;
                    color: rgb(78, 77, 77);
                }
                 
        </style>
         
        <div class="chartContainerDiv  w-full flex   items-center"  >
            <div class="chartContainerSubDiv w-full">
                <ul class="nav nav-tabs pt-2">
                    <li class="nav-item" >
                        <a class="nav-link disabled" aria-disabled="true" targetcontent="Overview" >Overview</a>
                    </li>
                    <li class="nav-item" >
                      <a class="nav-link active" aria-current="page" href="#CopyTrade" targetcontent="Gain" >Gain</a>
                    </li>
                    <li class="nav-item" >
                        <a class="nav-link" href="#CopyTrade" targetcontent="Monthly" >Monthly</a>
                    </li>   
                    <li class="nav-item" >
                        <a class="nav-link" href="#CopyTrade" targetcontent="Symbols" >Symbols</a>
                    </li> 
                    <li class="nav-item" >
                        <a class="nav-link" href="#CopyTrade" targetcontent="AdvancedStats" >Advanced Stats</a>
                    </li>  
                                        
                </ul>
                <div >
                    <div class="  ">
                        <div class="Overview nav-content  "  > 
                        </div>
                        <div class="Gain nav-content active   ">     
                            <ul class="HaidraTabs304">
                                <li class="HaidraTab GainPercent active" targetcontent="GainPercentContent" >Gain</li>
                                <li class="HaidraTab ProfitUSD "  targetcontent="ProfitUSDContent"  >Profit</li>
                                <li class="HaidraTab PipsPIP"  targetcontent="PipsPIPContent"  >Pips</li>
                                <li class="HaidraTab DrawdownPercent "  targetcontent="DrawdownPercentContent"  >Drawdown</li> 
                            </ul>

                            <figure class="highcharts-figure">
                                <div id="container"></div> 
                            </figure> 

                            <div class="HaidraTabContent GainPercentContent " >
                                 
                            </div>
                            <div class="HaidraTabContent ProfitUSDContent " >
                                
                            </div>
                            <div class="HaidraTabContent PipsPIPContent " >
                                 
                            </div>
                            <div class="HaidraTabContent DrawdownPercentContent " >

                            </div> 
                        </div>

                        <div class="Monthly nav-content   ">     
                            <ul class="HaidraTabs305">
                                <li class="HaidraTab5 ProfitPercent active" targetcontent="MonthlyPercentContent" >Percent</li>
                                <li class="HaidraTab5 ProfitUSD "  targetcontent="MonthlyUSDContent"  >USD</li> 
                            </ul>

                            <figure class="highcharts-figure">
                                <div id="Monthlycontainer"></div> 
                            </figure> 

                            <div class="MonthlyTabContent MonthlyPercentContent active" >
                                 
                            </div>
                            <div class="MonthlyTabContent MonthlyUSDContent " >
                                
                            </div> 
                        </div>

                        <div class="Symbols nav-content    ">
                            
                            <figure class="highcharts-figure">
                                <div id="containerSymbols" target="SymbolsContent"></div> 
                            </figure>
                        </div>
                        <div class="AdvancedStats nav-content  ">
                            
                            <figure class="highcharts-figure">
                                <div id="containerAdvancedStats" target="AdvancedStats"></div> 
                            </figure>
                        </div>
                        
                    </div>
                </div>
            </div>
             
        </div> 
    </div> 
</div>
<!-- ... -->
<!-- ... -->
<!-- ... -->
<!-- ... -->
<!-- ... -->
<!-- style advanced Statistics  ------------------------------------------------------------------------------------- -->
<style>
    .advancedStatisticsContainerDiv{ 
        height: auto;
        width: 89%; 
        min-width: 600px;
        border: 1px solid rgb(120, 153, 214); 
    }      
    .advancedStatisticsContainerDiv .nav-content{
        height: 0;
        visibility: hidden;
    }
    .advancedStatisticsContainerDiv .nav-content.active{
        height: auto;
        visibility: initial;
    }
    .advancedStatisticsContainerDiv .rowHaidar{
         
    }
    .advancedStatisticsContainerDiv .rowHaidarSub{
         
    }
    .advancedStatisticsContainerDiv .colHaidra{
        
        width: 50%;
        
    } 
    .advancedStatisticsContainerDiv .colHaidra tr.borderbottom{
       border-bottom: 1px solid rgba(120, 153, 214,0.3); 
      
    } 
    .advancedStatisticsContainerDiv .colHaidra td{
        padding: 5px;
        font-family: roboto,sans-serif!important;
        font-weight: 200;
        font-size: 13px;
    }
    .advancedStatisticsContainerDiv .colHaidra span{ 
        font-weight: 500;
        font-size: 14px;
    }
    .Profitability{
        height: 20px;
    }
    .Profitability1{
        background-color: #85e079 ; 
        height: 10px; 
    }
    .Profitability2{
        background-color: #fe7f7f ;
        
        height: 10px; 
    }
    .SummaryTable{
         
        width: 100%;
        text-align: center; 
        font-weight: 500;
        font-size: 12px;
    }
    .SummaryTable th{  font-size: 13px; font-weight: 400; }
    .SummaryTable .th1 th{  font-size: 14px; font-weight: 700; }
    .SummaryTable td{   font-weight: 400; }
    .DDR221{
        border-top: 1px solid #e5e7eb;
    }
    .DDR221:hover{
        background-color: #e5e7eb57;
    }
</style>
<!-- Trades -->
<?php 
    $NbTrades = $NbTrades;
    $TPips = $PipsPIPContentTabel[count($PipsPIPContentTabel)-1][0] +0.1;
    //dd($PipsPIPContentTabelEachOneSeparately);
    //dd($ProfitContentTabelEachOneSeparately);
    $negPip = 0;
    $iNegPip = 0;
    $posPip = 0;
    $iPosPip = 0; 
    $BestTradePip = [0,""];
    $WorstTradePip = [0,""];
    foreach ($PipsPIPContentTabelEachOneSeparately as $key => $pip) {
        if($pip[0]>=0){
            $posPip+=$pip[0] ;
            $iPosPip+=1;
        }else{
            $negPip +=$pip[0] ;
            $iNegPip+=1;
        }
        $time = $pip[1]; $time = substr($time,0,strpos($time,'T',0)); $time = substr($time,strpos($time,'-',0)+1);
        if($pip[0]>$BestTradePip[0]){$BestTradePip = [$pip[0],$time] ; }
        if($pip[0]<$WorstTradePip[0]){$WorstTradePip = [$pip[0],$time] ; }
    } 
    $posPipStr = 0;
    $negPipStr = 0;
    if($iPosPip!=0) $posPipStr = round($posPip/$iPosPip,2) ; 
    if($iNegPip!=0) $negPipStr = round($negPip/$iNegPip,2) ;
    

    $negProfit = 0;
    $iNegProfit = 0;
    $posProfit = 0;
    $iPosProfit = 0;
    foreach ($ProfitContentTabelEachOneSeparately as $key => $Profit_) {
        if($Profit_[0]>=0){
            $posProfit+=$Profit_[0] ;
            $iPosProfit+=1;
        }else{
            $negProfit +=$Profit_[0] ;
            $iNegProfit+=1;
        }
    }
    $posProfitStr = 0;
    $negProfitStr = 0;
    if($iPosProfit!=0) $posProfitStr = round($posProfit/$iPosProfit,2) ; 
    if($negProfitStr!=0) $negProfitStr = round($negProfit/$iNegProfit,2) ; 
    //echo "All Trades =".$NbTrades." LossTrades: ".$iNegProfit. " Profit Trades : ".$iPosProfit ;
    $negProfitStrPrsent = round($iNegProfit*100/$NbTrades,2);
    $posProfitStrPrsent = round($iPosProfit*100/$NbTrades,2);
    //------------------------------------------------------  $profit = (double)$o->profit + (double)$o->commission+ (double)$o->swap ;
    $Longs  = 0 ;
    $Shorts = 0;
    $LongsWin = 0; 
    $ShortsWin = 0;
    $LongsWinPrsent = 0;
    $ShortsWinPrsent = 0;
    //---------------------------
    $BestTrade = [0,""];
    $WorstTrade = [0,""];
    $AvgTradeLengthTabel = [];
    $AvgTradeLengthTabelProfit = [];
    foreach ($orders as $key => $o) {
        $p = (double)$o->profit ;
        $pp = (double)$o->profit + (double)$o->commission+ (double)$o->swap ;
        if( ($o->type=='Buy')||($o->type=='buy')||($o->type=='BUY') ){ // Longs 
            $Longs += 1;
            if($p>0) $LongsWin+=1; 
        }else{ // Shorts
            $Shorts += 1;
            if($p>0) $ShortsWin+=1; 
        }
        if($o->openPrice!=0){
            $time = $o->closeTime; $time = substr($time,0,strpos($time,'T',0)); $time = substr($time,strpos($time,'-',0)+1);
            if($pp>$BestTrade[0]){$BestTrade = [$pp,$time] ; }
            if($pp<$WorstTrade[0]){$WorstTrade = [$pp,$time] ; } 
            $ferst = strtotime ($o->openTime );
            $last = strtotime ($o->closeTime);
            $diftime= $last - $ferst ;
            array_push($AvgTradeLengthTabel,$diftime);
            array_push($AvgTradeLengthTabelProfit , [$diftime,$pp]) ;
            
            
             
        }
        
    }
    if($Longs!=0) $LongsWinPrsent = round($LongsWin*100/$Longs,2);
    if($Shorts!=0) $ShortsWinPrsent = round($ShortsWin*100/$Shorts,2);  
    $AvgTradeLength = 0;
    
    foreach ($AvgTradeLengthTabel as $key => $t) {
        $AvgTradeLength += $t ;
    }
    $AvgTradeLength =round( $AvgTradeLength/count($AvgTradeLengthTabel) , 3 ) ;
     
    $AvgTradeLengthString = $AvgTradeLength. "s" ;
    if($AvgTradeLength>60){ // ['12m20s']
        $AvgTradeLengthString = (int)($AvgTradeLength/60). "m"  .(int)(fmod($AvgTradeLength,60)). "s";
    }
    if($AvgTradeLength>3600){ // ['3h12m20s']
        $mm = (int)($AvgTradeLength/60) ;
        $hhm = (int)(fmod($mm,60));
        $hh = (int)($mm/60);
        $AvgTradeLengthString = $hh. "h" .$hhm. "m"   ;
    }
    //-------------------------------------------------------------------
    
?>
<!-- Summary -->
<?php  
    $Summary = [] ;
    foreach ($Symbols as $key => $ss) {
        $symbol = $ss[0];
        $LongsTrades = 0 ;
        $LongsPips = 0;
        $longProfit = 0;
        $ShortsTrades = 0 ;
        $ShortsPips = 0;
        $ShortsProfit = 0;
        $iWon = 0 ;
        $iLost =0 ;
        
        
        foreach ($orders as $key => $o) {
            
            if( $symbol == $o->symbol && $o->closePrice != 0 ){
                $digits = $o->digits ;
                
                foreach ($symbolpips as $key => $sPips){  
                    if(str_contains(strtolower($symbol) , $sPips[0])){
                        $digits = $sPips[1] ;
                        break;
                    }
                }
                $Pips = 0;
                $p_ = (double)$o->profit + (double)$o->commission+ (double)$o->swap ;
                if( ($o->type=='Buy')||($o->type=='buy')||($o->type=='BUY') ){ // Longs 
                    $LongsTrades+=1;
                    $Pips = $o->closePrice - $o->openPrice ; 
                    $LongsPips += round(  $Pips*pow(10,$digits) ,2 ) ;
                    $longProfit +=$p_;
                }else{ //Shorts
                    $ShortsTrades+=1;
                    $Pips =  $o->openPrice - $o->closePrice ;   
                    $ShortsPips += round(  $Pips*pow(10,$digits) ,2 )  ;
                    $ShortsProfit +=$p_;
                }
                
                if($p_>0){
                    $iWon+=1;
                }else{
                    $iLost+=1;
                }
                 
            } 
        }
        $win_p = round($iWon*100/($iWon+$iLost),1) ;
        $lost_p = round(  $iLost*100/($iWon+$iLost) ,1);
        $colort = "#00aa38";
        array_push($Summary,['symbol'=>$symbol,'longsTrades'=>$LongsTrades,'shortsTrades'=>$ShortsTrades,
                                'longsPips'=>$LongsPips,'shortsPips'=>$ShortsPips,'win'=>$iWon,'lost'=>$iLost,'win_p'=>$win_p,'lost_p'=>$lost_p,
                            'longProfit'=>$longProfit,'shortsProfit'=>$ShortsProfit]);
    }
    //dd($Summary);
?>
<!-- Hourly -->
<?php 
    // [Hours,Win,Loss]
    $HourlyTabel = [
        ['00',0,0], ['01',0,0],['02',0,0],['03',0,0],['04',0,0],['05',0,0],['06',0,0],['07',0,0],['08',0,0],['09',0,0],['10',0,0],
        ['11',0,0],['12',0,0],['13',0,0],['14',0,0],['15',0,0],['16',0,0],['17',0,0],['18',0,0],['19',0,0],['20',0,0],
        ['21',0,0],['22',0,0],['23',0,0]  ] ;
    $DailyTabel = [ ['Sun',0,0],['Mon',0,0],['Tue',0,0],['Wed',0,0],['Thu',0,0],['Thu',0,0],['Sat',0,0] ] ;
    foreach ($orders as $key => $o) {
        $time = $o->closeTime ;
        $hours = substr($time,strpos($time,'T',0)+1,2);
        $p = (double)$o->profit ;//+ (double)$o->commission+ (double)$o->swap ;
        for ($i = 0; $i < 23; $i++) {
            $h = $HourlyTabel[$i][0];
            if( ($o->closePrice != 0) && ($hours==$h) ){
                if(  $p  >= 0){
                    $HourlyTabel[$i][1] = $HourlyTabel[$i][1] + 1;
                }else{
                    $HourlyTabel[$i][2] = $HourlyTabel[$i][2] + 1;
                }
                break;
            }
            //echo "<br> Date" .$time;
        }
    }
    foreach ($orders as $key => $o) {
        $time = $o->closeTime ;
        $day = date('D', strtotime($time)  ) ;
        $p = (double)$o->profit ;//+ (double)$o->commission+ (double)$o->swap ;
        for ($i = 0; $i < 6; $i++) {
            $d = $DailyTabel[$i][0];
            if( ($o->closePrice != 0) && ($day==$d) ){
                if(  $p  >= 0){
                    $DailyTabel[$i][1] = $DailyTabel[$i][1] + 1;
                }else{
                    $DailyTabel[$i][2] = $DailyTabel[$i][2] + 1;
                }
                break;
            } 
        }
    }
    //dd($DailyTabel);
?>
<!-- Duration -->
<?php 
      
       //dd($AvgTradeLengthTabelProfit);
?>

<div class=" flex justify-center pt-2 ">
    <div class="advancedStatisticsContainerDiv flex justify-center"> 
        <div class="StatisticsContainerDiv  w-full  ">
            <div class="StatisticsContainerDivSub    ">
                    
                <ul class="nav nav-tabs pt-2">
                    <li class="nav-item" >
                        <a class="nav-link disabled" aria-disabled="true" targetcontent="AdvancedStatistics" >Advanced Statistics</a>
                    </li>
                    <li class="nav-item" >
                      <a class="nav-link active" aria-current="page" href="#CopyTrade" targetcontent="Trades" >Trades</a>
                    </li>
                    <li class="nav-item" >
                        <a class="nav-link  " href="#CopyTrade" targetcontent="Summary" >Summary</a>
                    </li> 
                    <li class="nav-item" >
                        <a class="nav-link  " href="#CopyTrade" targetcontent="Hourly" >Hourly</a>
                    </li>
                    <li class="nav-item" >
                        <a class="nav-link  " href="#CopyTrade" targetcontent="Daily" >Daily</a>
                        </li>
                    <li class="nav-item" >
                        <a class="nav-link  " href="#CopyTrade" targetcontent="Duration" >Duration</a>
                    </li> 
                    
                </ul>
                <div >
                    <div class="  ">
                        <div class="AdvancedStatistics nav-content " style="width: 0; height: 0;"></div>
                        
                        <div class="Trades nav-content active ">
                            <div class="rowHaidar" style=" ">
                                <div class="rowHaidarSub flex   "> 
                                    <div class="colHaidra  "  >
                                        <div style="height: 4px;"></div>
                                        <table class="table-hover table-small w-full    " style="border-right: 1px solid rgba(120, 153, 214,0.3);" >
                                            <tbody class=" w-full">
                                                <tr class="borderbottom"   >
                                                    <td><label class="custom-analysis-popover" data-title="Daily Gain" data-content="Daily compound rate of return leading to the total gain.">
                                                    <span class="dotted">
                                                        Trades:
                                                    </span>
                                                    </label></td>
                                                    <td class=" flex justify-end"><span>{{$NbTrades}}</span></td>
                                                </tr>
                                                <tr class="borderbottom border border-spacing-3 ">
                                                    <td><label class="custom-analysis-popover" data-title="Monthly Gain" data-content="Monthly compound rate of return leading to the total gain.">
                                                    <span class="dotted">
                                                        Profitability:
                                                    </span>
                                                    </label></td>
                                                    <td class=" flex justify-end ">
                                                        <span style="cursor: pointer;" data-popover-target="popover-default99" class="Profitability  flex justify-center items-center">
                                                            <div class="Profitability1" style="width: {{round($posProfitStrPrsent,0)}}px;"  ></div>
                                                            <div class="Profitability2" style="width: {{round($negProfitStrPrsent,0)}}px;"> </div>
                                                        </span >
                                                        <div  data-popover id="popover-default99" role="tooltip" class="absolute z-10  w-40 invisible inline-block  text-sm text-gray-500 transition-opacity duration-300 bg-white border border-gray-200 rounded-lg shadow-sm opacity-0 dark:text-gray-400 dark:border-gray-600 dark:bg-gray-800">
                                                             
                                                            <div class="px-3 py-2 ">
                                                                <p>Win {{$iPosProfit}} out of {{$NbTrades}} trades, which represents {{$posProfitStrPrsent}}%.
                                                                    <br/>
                                                                Lost {{$iNegProfit}} out of {{$NbTrades}} trades, which represents {{$negProfitStrPrsent}}%.</p>
                                                                 
                                                            </div>
                                                            <div data-popper-arrow></div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr class="borderbottom">
                                                    <td> Pips:</td>
                                                    <td class=" flex justify-end"><span> {{$TPips}} </span></td>
                                                </tr> 
                                                <tr class="borderbottom">
                                                    <td> Average Win:</td>
                                                    <td class=" flex justify-end"><span> {{$posPipStr}} pips / {{$posProfitStr}}$ </span></td>
                                                </tr>
                                                <tr class="borderbottom">
                                                    <td> Average Loss:</td>
                                                    <td class=" flex justify-end"><span>{{$negPipStr}} pips / {{$negProfitStr}}$</span></td>
                                                </tr>
                                                <tr class="borderbottom">
                                                    <td>  Lots :</td>
                                                    <td class=" flex justify-end"><span> {{round($TotalLotsSize,2)}} Lots </span></td>
                                                </tr>
                                                <tr class="">
                                                    <td>  Commissions:	</td>
                                                    <td class=" flex justify-end"><span> {{$TotalCommintion}}$</span></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="colHaidra "  >
                                        <div style="height: 4px;"></div>
                                        <table class="table-hover table-small w-full    " style="border-right: 1px solid rgba(120, 153, 214,0.3);" >
                                            <tbody class=" w-full">
                                                <tr class="borderbottom"   >
                                                    <td><label class="custom-analysis-popover" data-title="Daily Gain" data-content="Daily compound rate of return leading to the total gain.">
                                                    <span class="dotted">
                                                        Longs Won:
                                                    </span>
                                                    </label></td>
                                                    <td class=" flex justify-end" > <span class="px-1" style="color: rgba(78, 77, 77,0.4);">  ({{$LongsWin}}/{{$Longs}}) </span>  {{$LongsWinPrsent}}%  </td>
                                                </tr>
                                                <tr class="borderbottom">
                                                    <td><label class="custom-analysis-popover" data-title="Monthly Gain" data-content="Monthly compound rate of return leading to the total gain.">
                                                    <span class="dotted">
                                                        Shorts Won:
                                                    </span>
                                                    </label></td>
                                                    <td class=" flex justify-end"><span class="px-1" style="color: rgba(78, 77, 77,0.4);">  ({{$ShortsWin}}/{{$Shorts}}) </span>  {{$ShortsWinPrsent}}% </td>
                                                </tr>
                                                <tr class="borderbottom"> 
                                                    <td> Best Trade ($):</td>
                                                    <td class=" flex justify-end"><span class="px-1" style="color: rgba(78, 77, 77,0.4);">  ({{$BestTrade[1]}}) </span>  {{$BestTrade[0]}} </td>
                                                </tr>
                                                <tr class="borderbottom">
                                                    <td>  Worst Trade ($):</td>
                                                    <td class=" flex justify-end"><span class="px-1" style="color: rgba(78, 77, 77,0.4);">  ({{$WorstTrade[1]}}) </span>  {{$WorstTrade[0]}} </td>
                                                </tr>
                                                <tr class="borderbottom">
                                                    <td> Best Trade (Pips):</td>
                                                    <td class=" flex justify-end"><span class="px-1" style="color: rgba(78, 77, 77,0.4);">  ({{$BestTradePip[1]}}) </span>  {{$BestTradePip[0]}} </td>
                                                </tr>
                                                <tr class="borderbottom">
                                                    <td> Worst Trade (Pips):</td>
                                                    <td class=" flex justify-end"><span class="px-1" style="color: rgba(78, 77, 77,0.4);">  ({{$WorstTradePip[1]}}) </span>  {{$WorstTradePip[0]}} </td>
                                                </tr>
                                                <tr>
                                                    <td> Avg. Trade Length:</td>
                                                    <td class=" flex justify-end"><span>{{$AvgTradeLengthString}}</span></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- div class="colHaidra ">
                                        <div style="height: 4px;"></div>
                                        <table class="table-hover table-small w-full    "  " >
                                            <tbody class=" w-full">
                                                <tr class="borderbottom"   >
                                                    <td><label class="custom-analysis-popover" data-title="Daily Gain" data-content="Daily compound rate of return leading to the total gain.">
                                                    <span class="dotted">
                                                        Profit Factor:
                                                    </span>
                                                    </label></td>
                                                    <td class=" flex justify-end"><span> 2.5 </span></td>
                                                </tr>
                                                <tr class="borderbottom">
                                                    <td><label class="custom-analysis-popover" data-title="Monthly Gain" data-content="Monthly compound rate of return leading to the total gain.">
                                                    <span class="dotted">
                                                        Standard Deviation:
                                                    </span>
                                                    </label></td>
                                                    <td class=" flex justify-end"><span>$13.507</span></td>
                                                </tr>
                                                <tr class="borderbottom">
                                                    <td> Sharpe Ratio:</td>
                                                    <td class=" flex justify-end"><span>0.32 </span></td>
                                                </tr>
                                                <tr class="borderbottom">
                                                    <td> Z-Score (Probability):</td>
                                                    <td class=" flex justify-end"><span> -1.51 (87.43%) </span></td>
                                                </tr>
                                                <tr class="borderbottom">
                                                    <td> Expectancy:</td>
                                                    <td class=" flex justify-end"><span> 15.9 Pips / $3.51 </span></td>
                                                </tr>
                                                <tr class="borderbottom">
                                                    <td> AHPR:</td>
                                                    <td class=" flex justify-end"><span> 0.60%</span></td>
                                                </tr>
                                                <tr>
                                                    <td> GHPR:</td>
                                                    <td class=" flex justify-end"><span> 0.17% </span></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div -->
                                </div>
                            </div>
                        </div> 

                        <div class="Summary nav-content"> 
                            
                            <table class="SummaryTable    ">


                                <thead class=" ">
                                    <tr class="th1" >
                                        <th colspan="1" class=" "></th>
                                        <th colspan="3" class="font-semibold ">Longs</th>
                                        <th colspan="3" class=" ">Shorts</th>
                                        <th colspan="5" class=" ">Total</th>
                                         
                                    </tr>
                                    <tr>
                                        <th  >Currency</th>
                                        <th class=" border-l-2">Trades</th>
                                        <th class=" ">Pips</th>
                                        <th class=" ">Profit($)</th>
                                        <th class=" border-l-2 	">Trades</th>
                                        <th class="   ">Pips</th>
                                        <th class=" ">Profit($)</th>
                                        <th class=" border-l-2  ">Trades</th>
                                        <th class=" ">Pips</th>
                                        <th  >Profit($)</th>
                                        <th  >Won(%)</th>
                                        <th  >Lost(%)</th>
                                         
                                    </tr>
                                </thead>
                                @foreach ($Summary as $key => $su)
                                    <tbody class=" DDR221" >
                                        <tr  >
                                            <td  >{{$su['symbol']}} </td>
                                            <td class=" border-l-2 ">{{$su['longsTrades']}}</td>
                                            <td> <?php $colort="#00aa38";if($su['longsPips']<0) $colort="#ff3232";  ?>
                                                <span class=" " style="color: {{$colort}};">{{$su['longsPips']}}</span>
                                            </td>  <?php $colort="#00aa38";if($su['longProfit']<0) $colort="#ff3232";  ?>
                                            <td style="color: {{$colort}};>
                                                <span class=" " >{{$su['longProfit']}}</span>
                                            </td>
                                            <td class="border-l-2 ">{{$su['shortsTrades']}}</td>
                                                <?php $colort="#00aa38";if($su['shortsPips']<0) $colort="#ff3232";  ?>
                                            <td class="" style="color: {{$colort}};>
                                                <span class=" ">{{$su['shortsPips']}}</span>
                                            </td> <?php $colort="#00aa38";if($su['shortsProfit']<0) $colort="#ff3232";  ?>
                                            <td style="color: {{$colort}};>
                                                <span class="green">{{$su['shortsProfit']}}</span>
                                            </td>
                                            <td class="border-left border-l-2  ">{{ ($su['shortsTrades']+$su['longsTrades'])}}</td>
                                            <?php $colort="#00aa38";if(   ($su['shortsPips']+$su['longsPips']) <0) $colort="#ff3232";  ?>
                                            <td class="" style="color: {{$colort}};>
                                                <span class="green">{{( $su['shortsPips']+$su['longsPips'] ) }}</span>
                                            </td> <?php $colort="#00aa38";if(   ($su['shortsProfit']+$su['longProfit']) <0) $colort="#ff3232";  ?>
                                            <td style="color: {{$colort}};>
                                                <span class="green">{{ ( $su['shortsProfit']+$su['longProfit'] )}}</span>
                                            </td> 
                                            <td class=" " style="background-color:#dffedf ;" >{{$su['win']}} ({{$su['win_p']}}%)</td>
                                            <td class=" " style="background-color:#fee4e4 ;" >{{$su['lost']}} ({{$su['lost_p']}}%)</td>
                                             
                                        </tr>
                                         
                                    </tbody>
                                 @endforeach
                            </table>
                            
                        </div>

                        <div class="Hourly nav-content">    
                            <figure class="highcharts-figure">
                                <div id="containerHourly" target="AdvancedStats"></div> 
                            </figure>
                        </div>

                        <div class="Daily nav-content"> 
                            <figure class="highcharts-figure">
                                <div id="containerDaily" target="AdvancedStats"></div> 
                            </figure>
                        </div>
 

                        <div class="Duration nav-content"> 
                            <figure class="highcharts-figure">
                                <div id="TradeLengthv1" target="AdvancedStats"></div> 
                            </figure>
                        </div>
                        
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- ... -->
<!-- ... -->
<!-- ... -->
<!-- ... -->
<!-- ... -->
<!--  ------------------------------------------------------------------------------------- -->
<style>
    .TradingActivityContainerDiv{ 
        width: 89%; 
        border: 1px solid rgb(120, 153, 214);
        
    }      
    .TradingActivityContainerDiv .nav-content{
        height: 0;
        visibility: hidden;
    }
    .TradingActivityContainerDiv .nav-content.active{
        height: auto;
        visibility: initial;
    }
</style>


<div class=" flex justify-center pt-2 ">
    <div class="TradingActivityContainerDiv flex justify-center">
        <div class="TradingContainerDiv  w-full  ">
            <div class="TradingContainerDivSub    ">
                    
                <ul class="nav nav-tabs pt-2">
                    <li class="nav-item" >
                        <a class="nav-link disabled" aria-disabled="true" targetcontent="TradingActivity" >Trading Activity</a>
                    </li>
                    
                    <li class="nav-item" >
                        <a class="nav-link active OpenTrades_a" href="#CopyTrade" targetcontent="OpenTrades" >Open Trades(0) </a>
                    </li> 
                    <li class="nav-item" >
                        <a class="nav-link  " href="#CopyTrade" targetcontent="OpenOrders" >Open Orders(0) </a>
                    </li>
                    <li class="nav-item" >
                        <a class="nav-link  " href="#CopyTrade" targetcontent="History" >History({{count($orders)}})</a>
                    </li> 
                    
                </ul>
                <div >
                    <div class="  ">
                        <div class="gain1_{{$id}}"  style="visibility: hidden; width: 0;height: 0; "></div>
                        <div class="gain2_{{$id}}" style="visibility: hidden; width: 0;height: 0; "  ></div>

                        <div class="TradingActivity nav-content " style="width: 0; height: 0;"></div>
                         
                        <div class="History nav-content px-2 py-2 "> 
                            
                            <x-tables.histoty_v3ForSingelHistory lines="5"   login='{{$id}}' />

                        </div>  
                        <div class="OpenTrades HaidraDelete nav-content active flex items-center justify-center w-full"> 
                            <div class="Nodatatodisplay px-3 py-3 w-full">  
                                <x-tables.histoty-account-view-open-trades    
                                                                                token="{{$token }}" 
                                                                                type="{{$account->type }}"
                                                                                urls="{{$rest_api_full_MT5  }}"
                                                                                loginx="{{ $account->login }}"
                                                                                />
                            </div>
                        </div> 
                        <div class="OpenOrders nav-content  flex items-center justify-center">    
                             <div class="Nodatatodisplay px-3 py-3"> No data to display</div>
                        </div> 
                        
                        
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>






































@foreach ($HourlyTabel as $key => $h)
<script>  HourlyTabel.push( [    '{{$h[0] }}'   ,  {{ $h[1] }}  ,{{$h[2]}}    ] ); xHourlyTabel.push('{{$h[0] }}');yHourlyTabel.push({{$h[1] }});zHourlyTabel.push({{$h[2] }});</script>
@endforeach 

@foreach ($DailyTabel as $key => $h)
<script>  DailyTabel.push( [    '{{$h[0] }}'   ,  {{ $h[1] }}  ,{{$h[2]}}    ] ); xDailyTabel.push('{{$h[0] }}');yDailyTabel.push({{$h[1] }});zDailyTabel.push({{$h[2] }});</script>
@endforeach

@foreach ($AvgTradeLengthTabelProfit as $key => $h)
<script>  AvgTradeLengthTabelProfit.push( [    {{$h[0] }}   ,  {{ $h[1] }} ]   ); </script>
@endforeach 

























































<!-- document.querySelectorAll .nav-link -->
<script>

    document.querySelectorAll('.infoPortlet .nav-link').forEach( (e,index) => {
        e.addEventListener('click',()=>{
            //e.classList.toggle('active');
            
            document.querySelectorAll('.infoPortlet .nav-link').forEach( (j,index) => {
                j.classList.remove('active');
            });
            document.querySelectorAll('.infoPortlet .nav-content').forEach( (j,index) => {
                j.classList.remove('active');
            });
            e.classList.add('active'); 
            let target ='.infoPortlet .' + e.getAttribute("targetcontent");
              
            let h = document.querySelector(target);
             
            h.classList.add('active'); 
            
        });
    } );
     


    document.querySelectorAll('.chartContainerSubDiv .nav-link').forEach( (e,index) => {
        e.addEventListener('click',()=>{
            //e.classList.toggle('active'); 
            document.querySelectorAll('.chartContainerSubDiv .nav-link').forEach( (j,index) => {
                j.classList.remove('active');
            });
            document.querySelectorAll('.chartContainerSubDiv .nav-content').forEach( (j,index) => {
                j.classList.remove('active');
            });
            e.classList.add('active'); 
            let target ='.chartContainerSubDiv .' + e.getAttribute("targetcontent");
              
            let h = document.querySelector(target);
             
            h.classList.add('active'); 
            
        });
    } );


    document.querySelectorAll('.chartContainerSubDiv').forEach( (e,index) => {
        
        document.querySelectorAll('.chartContainerSubDiv .nav-content').forEach( (j,index) => {
            //console.log("j  = "+j.offsetWidth+ " . e = "+e.offsetWidth);
            j.style.width =  e.offsetWidth -20  + "px";
            //console.log("j  = "+j.offsetWidth+ " . e = "+e.offsetWidth);
            });
    });


    window.addEventListener('resize',()=>{
        document.querySelectorAll('.chartContainerSubDiv').forEach( (e,index) => {
        
        document.querySelectorAll('.chartContainerSubDiv .nav-content').forEach( (j,index) => {
            //console.log("j  = "+j.offsetWidth+ " . e = "+e.offsetWidth);
            j.style.width =  e.offsetWidth -4  + "px";
            //console.log("j  = "+j.offsetWidth+ " . e = "+e.offsetWidth);
            });
        });

    });

 
    document.querySelectorAll('.HaidraTabs304 .HaidraTab').forEach( (e,index) => {
        e.addEventListener('click',()=>{ 
            document.querySelectorAll('.chartContainerSubDiv .HaidraTabs304 .HaidraTab').forEach( (j,index) => {    j.classList.remove('active');   });
            document.querySelectorAll('.chartContainerSubDiv .HaidraTabs304 .HaidraTabContent').forEach( (j,index) => {  j.classList.remove('active');   });
            e.classList.add('active'); 
            let target ='.chartContainerSubDiv .' + e.getAttribute("targetcontent");
            document.querySelector(target).classList.add('active');    
            console.log(e.getAttribute("targetcontent"));
            if( e.getAttribute("targetcontent") == "GainPercentContent" ){
                GainPercentContentHighcharts(TWRrate);
            }
            if( e.getAttribute("targetcontent") == "ProfitUSDContent" ){
                ProfitUSDContentHighcharts(Profits);
            }
            if( e.getAttribute("targetcontent") == "PipsPIPContent" ){
                PipsPIPContentHighcharts(Pips);
            }
            if( e.getAttribute("targetcontent") == "DrawdownPercentContent" ){
                DrawdownPercentContentHighcharts(MaxDDDayly);
            }
        });
    } );
    //  2024 Mod
    document.querySelectorAll('.HaidraTabs305 .HaidraTab5').forEach( (e,index) => {
        e.addEventListener('click',()=>{ 
            document.querySelectorAll('.chartContainerSubDiv .HaidraTabs305 .HaidraTab5').forEach( (j,index) => {    j.classList.remove('active');   });
            document.querySelectorAll('.chartContainerSubDiv .HaidraTabs305 .MonthlyTabContent').forEach( (j,index) => {  j.classList.remove('active');   });
            e.classList.add('active'); 
            let target ='.chartContainerSubDiv .' + e.getAttribute("targetcontent");
            document.querySelector(target).classList.add('active');    
            console.log(e.getAttribute("targetcontent"));
            if( e.getAttribute("targetcontent") == "MonthlyPercentContent" ){
                MonthlyPercentContentHighcharts(TWRrate);
            }
            if( e.getAttribute("targetcontent") == "MonthlyUSDContent" ){
                MonthlyUSDContent(Profits,Profits_for_Monthly);
            }
             
        });
    } );

</script>
<!-- document.querySelectorAll advancedStatisticsContainerDiv + Sub -->
<script>
    document.querySelectorAll('.StatisticsContainerDivSub .nav-link').forEach( (e,index) => {
        e.addEventListener('click',()=>{
            //e.classList.toggle('active');
            
            document.querySelectorAll('.StatisticsContainerDivSub .nav-link').forEach( (j,index) => {
                j.classList.remove('active');
            });
            document.querySelectorAll('.StatisticsContainerDivSub .nav-content').forEach( (j,index) => {
                j.classList.remove('active');
            });
            e.classList.add('active'); 
            let target ='.StatisticsContainerDivSub .' + e.getAttribute("targetcontent");
              
            let h = document.querySelector(target);
             
            h.classList.add('active'); 
            
        });
    } );
    document.querySelectorAll('.TradingContainerDivSub .nav-link').forEach( (e,index) => {
        e.addEventListener('click',()=>{
            //e.classList.toggle('active');
            
            document.querySelectorAll('.TradingContainerDivSub .nav-link').forEach( (j,index) => {
                j.classList.remove('active');
            });
            document.querySelectorAll('.TradingContainerDivSub .nav-content').forEach( (j,index) => {
                j.classList.remove('active');
            });
            e.classList.add('active'); 
            let target ='.TradingContainerDivSub .' + e.getAttribute("targetcontent");
              
            let h = document.querySelector(target);
             
            h.classList.add('active'); 
            
        });
    } );
    function tradeLengthString(value) {
                        let tradeLength = value ;
                        let tradeLengthString = tradeLength +"s";
                        if(tradeLength>60){
                            tradeLengthString = parseInt(tradeLength/60) +"m"+parseInt( tradeLength%60 )+"s";
                        }
                        if(tradeLength>3600){
                            let mm = parseInt(tradeLength/60);
                            let hhm = parseInt( mm%60 ) ;
                            let hh = parseInt( mm/60 ) ;
                            tradeLengthString = hh+"h"+hhm+"m";
                        }
                        if(tradeLength>86400){
                            let dd = parseInt(tradeLength/86400);
                            let lefttime = tradeLength- dd*86400 ;
                            let hh = parseInt( lefttime/3600 ) ;
                            tradeLengthString = dd+"d"+hh+"m";
                        }
                        
                        return tradeLengthString;
    } 
</script>
<!--annotations10 toTimeProfits GainPercentContentHighcharts   -->
<script>


    //console.log(HourlyTabel);

    //annotations10(Profits,TimeProfits);
    function annotations10(data,TimeProfits){ 
        let nb = data.length;
        console.log(" chart has  = "+nb+" Trades");
        Highcharts.chart('container', {

            chart: {
                type: 'area',
                zoomType: 'x',
                panning: true,
                panKey: 'shift',
                scrollablePlotArea: {
                    minWidth: 600
                }
            }, 
            title: {
                text: 'HFTHaidra',
                align: 'left'
            }, 
            lang: {
                accessibility: {
                    screenReaderSection: {
                        annotations: {
                            descriptionNoPoints: '{annotationText}, at distance {annotation.options.point.x}km, elevation {annotation.options.point.y} meters.'
                        }
                    }
                }
            }, 
            
            credits: {
                enabled: false
            }, 
            xAxis: {
                type: 'datetime',    
                labels: {
                // Format the date
                    formatter: function() {
                        return Highcharts.dateFormat('  %b %d', this.value);
                    }
                },
            }, 
            yAxis: {

                
                title: {
                    text: 'Date Range'        
                },
                labels: {
                    format: '{value} $'
                }, 
                 
            }, 
            tooltip: {
                
                pointFormat: 'Live Profit ($)  {point.y} $.',
                headerFormat: ' Date : {point.x:%Y %b %d %H:%M:%S}  <br>',
                shared: true, 
            }, 
            series: [{
                data: data,
                lineColor: Highcharts.getOptions().colors[1],
                color: Highcharts.getOptions().colors[2],
                fillOpacity: 0.5,
                name: 'Elevation',
                marker: {
                    enabled: false
                },
                threshold: null
            }] ,

            });

    } // Fin Function .... 
    
    function toTimeProfits(idate){
        let res =  TimeProfits[idate] ;
        return res;
    }
    function toTimeTWRrate(idate){
        let res =  TimeTWRrate[idate] ;
        return res;
    }
    function toTimePips(idate){
        let res =  TimePips[idate] ;
        return res;
    }

    function GainPercentContentHighcharts(data){ 
        let nb = data.length; 
        console.log(" chart has  = "+nb+" Trades");
        
        Highcharts.chart('container', {

            chart: {
                type: 'area',
                zoomType: 'x',
                panning: true,
                panKey: 'shift',
                scrollablePlotArea: {
                    minWidth: 600
                }
            }, 
            title: {
                text: '',
                align: 'left'
            }, 
            lang: {
                accessibility: {
                    screenReaderSection: {
                        annotations: {
                            descriptionNoPoints: '{annotationText}, at distance {annotation.options.point.x}km, elevation {annotation.options.point.y} meters.'
                        }
                    }
                }
            }, 
            
            credits: {
                enabled: false
            }, 
            xAxis: { 
                labels: {
                    format: '{value} '
                }, 
            }, 
            yAxis: {
                title: {
                    text: ''        
                },
                labels: {
                    format: '{value} %  '
                }, 
                 
            }, 
            tooltip: {
                backgroundColor: "rgba(255,255,255,0.87)",
                formatter: function () {
                    var s = '<b>' + toTimeTWRrate( this.x ) + '</b>';

                    $.each(this.points, function () {
                        //color:rgba(0,230,99,0.87);
                        s += '<br/> Profit : <p style="color:rgb(93,91,209);">' +  this.y + '%</p>';
                    });

                    return s;
                },
                shared: true
                },
            plotOptions: {
               
                 
                area: {
                    
                    fillColor: {
                        linearGradient: {
                            x1: 0,
                            y1: 0,
                            x2: 0,
                            y2: 1
                        },
                        stops: [
                            [0, Highcharts.getOptions().colors[0]],
                            [1, Highcharts.color(Highcharts.getOptions().colors[0]).setOpacity(0).get('rgba')]
                        ]
                    },
                    marker: {
                        radius: 2
                    },
                    lineWidth: 1,
                    states: {
                        hover: {
                            lineWidth: 1
                        }
                    },
                    threshold: null
                }
            },
            series: [{
                data: data,
                lineColor: Highcharts.getOptions().colors[1],
                color: Highcharts.getOptions().colors[2],
                fillOpacity: 0.5,
                name: 'GAIN CHART',
                marker: {
                    enabled: false
                },
                threshold: null
            }] ,

            });

    }

    function ProfitUSDContentHighcharts(data){ 
        let nb = data.length; 
        console.log(" chart has  = "+nb+" Trades");
         
        
        let FullData = []; 
        data.forEach(( d, index ) => {    FullData.push( [  index    ,  d[1]  ] );  });
        
        Highcharts.chart('container', {

            chart: {
                type: 'area',
                zoomType: 'x',
                panning: true,
                panKey: 'shift',
                scrollablePlotArea: {
                    minWidth: 600
                }
            }, 
            title: {
                text: '',
                align: 'left'
            }, 
            lang: {
                accessibility: {
                    screenReaderSection: {
                        annotations: {
                            descriptionNoPoints: '{annotationText}, at distance {annotation.options.point.x}km, elevation {annotation.options.point.y} meters.'
                        }
                    }
                }
            }, 
            
            credits: {
                enabled: false
            }, 
            labels: {
                    format: '{value} '
                },
            xAxis: { 
                labels: {
                    format: '{value} '
                }, 
            }, 
            yAxis: {
                title: {
                    text: ''        
                },
                labels: {
                    format: '{value} $  '
                }, 
                 
            }, 
            tooltip: {
                backgroundColor: "rgba(255,255,255,0.87)",
                formatter: function () {
                    var s = '<b>' + toTimeProfits( this.x ) + '</b>';

                    $.each(this.points, function () {
                        //color:rgba(0,230,99,0.87);
                        s += '<br/> Profit : <p style="color:rgb(93,91,209);">' +  this.y + '$</p>';
                    });

                    return s;
                },
                shared: true
                },
            plotOptions: {
               
                 
                area: {
                    
                    fillColor: {
                        linearGradient: {
                            x1: 0,
                            y1: 0,
                            x2: 0,
                            y2: 1
                        },
                        stops: [
                            [0, Highcharts.getOptions().colors[0]],
                            [1, Highcharts.color(Highcharts.getOptions().colors[0]).setOpacity(0).get('rgba')]
                        ]
                    },
                    marker: {
                        radius: 2
                    },
                    lineWidth: 1,
                    states: {
                        hover: {
                            lineWidth: 1
                        }
                    },
                    threshold: null
                }
            }, 
            series: [{
                data: FullData,
                lineColor: Highcharts.getOptions().colors[1],
                color: Highcharts.getOptions().colors[2],
                fillOpacity: 0.5,
                name: 'PROFIT CHART',
                marker: {
                    enabled: false
                },
                threshold: null
            }] ,

            });

    }

    function PipsPIPContentHighcharts(data){ 
        let nb = data.length;
        console.log(" chart has  = "+nb+" Trades");
        Highcharts.chart('container', {

            chart: {
                type: 'area',
                zoomType: 'x',
                panning: true,
                panKey: 'shift',
                scrollablePlotArea: {
                    minWidth: 600
                }
            }, 
            title: {
                text: '',
                align: 'left'
            },
            lang: {
                accessibility: {
                    screenReaderSection: {
                        annotations: {
                            descriptionNoPoints: '{annotationText}, at distance {annotation.options.point.x}km, elevation {annotation.options.point.y} meters.'
                        }
                    }
                }
            },  
            credits: {
                enabled: false
            }, 
            xAxis: { 
                labels: {
                    format: '{value} '
                }, 
            }, 
            yAxis: {
                title: {
                    text: ''        
                },
                labels: {
                    format: '{value} Pip  '
                }, 
                 
            }, 
            tooltip: {
                backgroundColor: "rgba(255,255,255,0.87)",
                formatter: function () {
                    var s = '<b>' + toTimePips( this.x ) + '</b>'; 
                    $.each(this.points, function () { 
                        s += '<br/>   <p style="color:rgb(93,91,209);">' +  this.y + ' pip</p>';
                    }); 
                    return s;
                },
                shared: true
                },
            plotOptions:{ 
                area: { 
                    fillColor: {
                        linearGradient: {
                            x1: 0,
                            y1: 0,
                            x2: 0,
                            y2: 1
                        },
                        stops: [
                            [0, Highcharts.getOptions().colors[0]],
                            [1, Highcharts.color(Highcharts.getOptions().colors[0]).setOpacity(0).get('rgba')]
                        ]
                    },
                    marker: {
                        radius: 2
                    },
                    lineWidth: 1,
                    states: {
                        hover: {
                            lineWidth: 1
                        }
                    },
                    threshold: null
                }
            }, 
            series: [{
                data: data,
                lineColor: Highcharts.getOptions().colors[1],
                color: Highcharts.getOptions().colors[2],
                fillOpacity: 0.5,
                name: 'PROFIT(Pips) CHART',
                marker: {
                    enabled: false
                },
                threshold: null
            }] , 
            }); 
    }
    
    function DrawdownPercentContentHighcharts(data){
        
        // Create the chart
        Highcharts.chart('container', {
            chart: {
                type: 'column',
                zoomType: 'x',
                panning: true,
                panKey: 'shift',
                scrollablePlotArea: {
                    minWidth: 600
                }
            },
            title: { 
                text: ''
            },
             
             
            xAxis: {
                type: 'category',
                dateTimeLabelFormats: {
                day: '%e %b %y',
                },
            },
            yAxis: {
                title: {
                    text: 'Drawdown'
                }

            },
            legend: {
                enabled: false
            },
            plotOptions: {
                 
            },

            tooltip: {
                headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
                pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y:.2f}%</b><br/>'
            },

            series: [
                {
                    data: data,
                    colorByPoint: true,
                    name: 'Drawdown(%)', 
                    
                }
            ],
            drilldown: {
                breadcrumbs: {
                    position: {
                        align: 'right'
                    }
                },
                
            }
        });

    }

    function SymbolsContentHighcharts(data){
        
        // Create the chart
        Highcharts.chart('containerSymbols', {
            chart: {
                type: 'pie',
                zoomType: 'x',
                panning: true,
                panKey: 'shift',
                scrollablePlotArea: {
                    minWidth: 600
                }
            },
            title: { 
                text: ' BROWSING SYMBOLS '
            },
             
             
            xAxis: {
                type: 'category',
                 
            },
            yAxis: {
                type: 'category',
                title: {
                    text: 'Drawdown'
                }

            },
            legend: {
                enabled: false
            },
            plotOptions: {
                 
            },

            tooltip: {
                headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
                pointFormat: '<span style="color:{point.color}">{point.name}  </span>: <b>{point.y} Trade</b><br/>'
                 
            },

            series: [
                {
                    data: data,
                    colorByPoint: true,
                    name: 'Currency Popularity(Nb Trades)', 
                    
                }
            ],
            drilldown: {
                breadcrumbs: {
                    position: {
                        align: 'right'
                    }
                },
                
            }
        });

    }

    function AdvancedStatsHighcharts(data){
        
        // Create the chart
        Highcharts.chart('containerAdvancedStats', {
             
            chart: {
                type: 'column'
            },

            rangeSelector: {
                selected: 1
            },

            title: {
                text: ''
            },
            xAxis: {
                type: 'category',
                dateTimeLabelFormats: {
                day: '%e %b %y',
            },
            },
            yAxis: {
                title: {
                    text: 'Profit($)'
                }

            },
            tooltip: {
                //headerFormat: '<span style="font-size:11px">{point.x}</span><br>',
                pointFormat: '<b>Profit:</b><span style="color:{point.color}"> {point.y:.2f} $</span> <br/>'
            },
            series: [{
                type: 'column',
                name: 'Daily Profit',
                data: data,
                
            }]
        });

    }
    
    function HourlyHighcharts(HourlyTabel,xHourlyTabel,yHourlyTabel,zHourlyTabel){
     
        Highcharts.chart('containerHourly', {
            chart: {
                type: 'column'
            },
            title: {
                text: ''
            },
            xAxis: {
                categories: xHourlyTabel
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Trades'
                },
                stackLabels: {
                    enabled: true
                }
            },
            legend: {
                align: 'left',
                x: 70,
                verticalAlign: 'top',
                y: 70,
                floating: true,
                backgroundColor:
                    Highcharts.defaultOptions.legend.backgroundColor || 'white',
                borderColor: '#CCC',
                borderWidth: 1,
                shadow: false
            },
            tooltip: {
                headerFormat: '<b> On {point.x}</b><br/>',
                pointFormat: '{series.name}: {point.y}({point.percentage:.0f}%)<br/>Total: {point.stackTotal}'
            },
            plotOptions: {
                column: {
                    stacking: 'normal',
                    dataLabels: {
                        enabled: true
                    }
                }
            },
            series: [{
                name: 'Winning',
                data: yHourlyTabel
            }, {
                name: 'Losing',
                data: zHourlyTabel
            } ]
        });

    }

    function DailyTabelHighcharts(DailyTabel,xDailyTabel,yDailyTabel,zDailyTabel){
        
    
        Highcharts.chart('containerDaily', {
            chart: {
                type: 'column'
            },
            title: {
                text: ''
            },
            xAxis: {
                categories: xDailyTabel
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Trades'
                },
                stackLabels: {
                    enabled: true
                }
            },
            legend: {
                align: 'left',
                x: 70,
                verticalAlign: 'top',
                y: 70,
                floating: true,
                backgroundColor:
                    Highcharts.defaultOptions.legend.backgroundColor || 'white',
                borderColor: '#CCC',
                borderWidth: 1,
                shadow: false
            },
            tooltip: {
                headerFormat: '<b> On {point.x}</b><br/>',
                pointFormat: '{series.name}: {point.y}({point.percentage:.0f}%)<br/>Total: {point.stackTotal}'
            },
            plotOptions: {
                column: {
                    stacking: 'normal',
                    dataLabels: {
                        enabled: true
                    }
                }
            },
            series: [{
                name: 'Winning',
                data: yDailyTabel
            }, {
                name: 'Losing',
                data: zDailyTabel
            } ]
        });

    }
    
    function TradeLengthHighcharts(data){  

        Highcharts.chart('TradeLengthv1', {

            chart: {
                type: 'scatter',
                zoomType: 'xy',
                 
            }, 
            title: {
                text: '',
                align: 'left'
            }, 
           
            labels: {
                    format: '{value} '
                },
            xAxis: {
                title: {
                    text: ''
                }, 
                labels: {
                    formatter: function () {
                        let tradeLength = this.value ;
                        let tradeLengthString = tradeLength +"s";
                        if(tradeLength>60){
                            tradeLengthString = parseInt(tradeLength/60) +"m"+parseInt( tradeLength%60 )+"s";
                        }
                        if(tradeLength>3600){
                            let mm = parseInt(tradeLength/60);
                            let hhm = parseInt( mm%60 ) ;
                            let hh = parseInt( mm/60 ) ;
                            tradeLengthString = hh+"h"+hhm+"m";
                        }
                        if(tradeLength>86400){
                            let dd = parseInt(tradeLength/86400);
                            let lefttime = tradeLength- dd*86400 ;
                            let hh = parseInt( lefttime/3600 ) ;
                            tradeLengthString = dd+"d"+hh+"m";
                        }
                        
                        return tradeLengthString;
                    }
                }, 
                 
            },
            yAxis: {
                title: {
                    text: 'Profits($)'
                },
                labels: {
                    format: '{value} $  '
                }
            },  
            
            tooltip: {
                backgroundColor: "rgba(255,255,255,0.87)",
                formatter: function () {
                    var s = '<b> Trade Long Time :' +   tradeLengthString(this.x)    + '</b>'; 
                     
                        s += '<br/> Profits :  <p style="color:rgb(93,91,209);">' +  this.y + '$</p>';
                     
                    return s;
                },
                shared: true
                },  
            series: [{
                name: 'transactions',
                data: data,
                 
            }] ,

            });

    }
    

    function MonthlyPercentContentHighcharts(data){ 
        let nb = data.length; 
        let MonthlyWinTWR = []; 
        let MonthlyWinPres = []; 
        let categories = [];
        let oldDate = TimeTWRrate[0].substring(0,7);
         
        MonthlyWinTWR.push([  oldDate, 0 ]); 
        console.log(  );
         
        for(let k =1;k<data.length;k++)
        {   
            // Calculation ...
            let date1 = TimeTWRrate[k].substring(0,7);
            if(date1!=oldDate){
                MonthlyWinTWR.push([ TimeTWRrate[k-1].substring(0,7) , data[k-1][1] ]);
                oldDate = date1;
            }
            //console.log(k," ). Time : ", date1 ," -> ",data[k] );
            //FullData.push( [  index    ,  d[1]  ] );  
        } 
        MonthlyWinTWR.push([  TimeTWRrate[data.length-1].substring(0,7),data[data.length-1][1]  ]); 
        //console.log(MonthlyWinTWR);
        
        for(let k =1;k<MonthlyWinTWR.length;k++)
        {  
            //MonthlyWinPres.push([MonthlyWinTWR[k][0], (MonthlyWinTWR[k][1]- MonthlyWinTWR[k-1][1]).toFixed(2) ]);
            let v = parseFloat ( (MonthlyWinTWR[k][1]- MonthlyWinTWR[k-1][1]).toFixed(2) );
             
            //console.log(v,typeof( v ));
            MonthlyWinPres.push([k, v ]);
            categories.push((MonthlyWinTWR[k][1]- MonthlyWinTWR[k-1][1]).toFixed(2) );
            //console.log('p1: ',MonthlyWinTWR[k][1],' p2: ',MonthlyWinTWR[k-1][1]);
        }
        //console.log(" chart MonthlyWinTWR has  = "+MonthlyWinTWR.length+" Bars ");
        //console.log(MonthlyWinPres);

        

            
        Highcharts.chart('Monthlycontainer', {
                chart: {
                    type: 'column',
                },
                title: {
                    text: ''
                },
                legend: false,
                tooltip: {
                backgroundColor: "rgba(255,255,255,0.87)",
                formatter: function () {
                    var s = '<b>' + MonthlyWinTWR[this.x][0] + '</b>';

                    $.each(this.points, function () { 
                        s += '<br/> Profit : <p style="color:rgb(93,91,209);">' +  this.y + '%</p>';
                    });

                    return s;
                },
                shared: true
                },
                xAxis: {
                    labels: {
                    enabled: false
                    },
                    labels: {
                        formatter: function () {
                            var s = '<br/><p style="color:rgb(93,91,209);">'+MonthlyWinTWR[this.value][0]+'</p>';

                           

                            return s;
                        },
                    }, 
                    //tickLength: 0
                },
                yAxis: {
                    title: {
                        text: ''        
                    },
                    labels: {
                        format: '{value} %  '
                    }, 
                    
                    //tickInterval: 10,
                },
                plotOptions: {
               
                 
                        area: {
                            
                            fillColor: {
                                linearGradient: {
                                    x1: 0,
                                    y1: 0,
                                    x2: 0,
                                    y2: 1
                                },
                                stops: [
                                    [0, Highcharts.getOptions().colors[0]],
                                    [1, Highcharts.color(Highcharts.getOptions().colors[0]).setOpacity(0).get('rgba')]
                                ]
                            },
                            marker: {
                                radius: 2
                            },
                            lineWidth: 1,
                            states: {
                                hover: {
                                    lineWidth: 1
                                }
                            },
                            threshold: null
                        }
                    }, 
                series: [{
                    data: MonthlyWinPres,
                    color: 'green',
                    negativeColor: 'red',
                    animation: true,
                     
                }]
        });
             


             
 
    
    
    
    
    
    }

    function MonthlyUSDContent(data,Profits_for_Monthly_){ 
        let nb = data.length; 
        let MonthlyWinTWR = []; 
        let MonthlyWinPres = []; 
        let categories = [];
        let oldDate = TimeProfits[0].substring(0,7);
        //console.log( "data =>" ,data ); 
        //console.log("Profits_for_Monthly => ",Profits_for_Monthly);
        MonthlyWinTWR.push([  oldDate, 0 ]); 
        //console.log(' Date:'+oldDate +" Balance :"+data[0][1]); 
        for(let k =1;k<data.length;k++)
        {   
            // Calculation ...
            let date1 = TimeProfits[k].substring(0,7);
            if(date1!=oldDate){
                MonthlyWinTWR.push([ TimeProfits[k-1].substring(0,7) , data[k-1][1] ,Profits_for_Monthly_[k][2] , Profits_for_Monthly_[k][3] ]);
                oldDate = date1;
                //console.log(' Date:'+oldDate +" Balance :"+data[k-1][1]); 
                //console.log("Deposits: ",Profits_for_Monthly_[k][2]," Withdrawals: ",Profits_for_Monthly_[k][3]);
                
            }
            //console.log(k," ). Time : ", date1 ," -> ",data[k] );
            //FullData.push( [  index    ,  d[1]  ] );  
        } 
        MonthlyWinTWR.push([  TimeProfits[data.length-1].substring(0,7),data[data.length-1][1],Profits_for_Monthly_[data.length-1][2],Profits_for_Monthly_[data.length-1][3]  ]); 
        //console.log(' Date:'+oldDate +" Balance :"+data[data.length-1][1]); 
        //console.log("Deposits: ",Profits_for_Monthly_[data.length-1][2]," Withdrawals: ",Profits_for_Monthly_[data.length-1][3]);
        console.log(MonthlyWinTWR);
        
        for(let k =1;k<MonthlyWinTWR.length;k++)
        {  
            //MonthlyWinPres.push([MonthlyWinTWR[k][0], (MonthlyWinTWR[k][1]- MonthlyWinTWR[k-1][1]).toFixed(2) ]);
            let v = parseFloat ( (MonthlyWinTWR[k][1]- MonthlyWinTWR[k-1][1]).toFixed(2) );
             
            //console.log(v,typeof( v ));
            MonthlyWinPres.push([k, v ]);
            let t = (MonthlyWinTWR[k][1]- MonthlyWinTWR[k-1][1]).toFixed(2) ;
            categories.push(t);
            console.log('[',MonthlyWinTWR[k][2],',',MonthlyWinTWR[k][3],']','(',MonthlyWinTWR[k][1],',',MonthlyWinTWR[k-1][1], ")=> ",t);
        }
        console.log(" chart MonthlyWinTWR has  = "+MonthlyWinTWR.length+" Bars " );
        
        Highcharts.chart('Monthlycontainer', {
                chart: {
                    type: 'column',
                },
                title: {
                    text: ''
                },
                legend: false,
                tooltip: {
                backgroundColor: "rgba(255,255,255,0.87)",
                formatter: function () {
                    var s = '<b>' + MonthlyWinTWR[this.x][0]+ '</b>';

                    $.each(this.points, function () {
                        //color:rgba(0,230,99,0.87);
                        s += '<br/> Profit : <p style="color:rgb(93,91,209);">' +  this.y + '$ </p>';
                    });

                    return s;
                },
                shared: true
                },
                xAxis: {
                    labels: {
                    enabled: false
                    },
                    labels: {
                        formatter: function () {
                            var s = '<br/><p style="color:rgb(93,91,209);">'+MonthlyWinTWR[this.value][0]+'</p>'; 
                            return s;
                        },
                    }, 
                     
                },
                yAxis: {
                    title: {
                        text: ''        
                    },
                    labels: {
                        format: '{value} $  '
                    }, 
                     
                    //tickInterval: 10,
                },
                plotOptions: {
               
                 
                        area: {
                            
                            fillColor: {
                                linearGradient: {
                                    x1: 0,
                                    y1: 0,
                                    x2: 0,
                                    y2: 1
                                },
                                stops: [
                                    [0, Highcharts.getOptions().colors[0]],
                                    [1, Highcharts.color(Highcharts.getOptions().colors[0]).setOpacity(0).get('rgba')]
                                ]
                            },
                            marker: {
                                radius: 2
                            },
                            lineWidth: 1,
                            states: {
                                hover: {
                                    lineWidth: 1
                                }
                            },
                            threshold: null
                        }
                    }, 
                    
                series: [{
                    data: MonthlyWinPres,
                    color: 'green',
                    negativeColor: 'red',
                    animation: true,
                     
                }]
        });
             


    

    }
    
</script>
















































<script>  

    document.querySelectorAll('.highcharts-credits').forEach( (e,index) => {
        e.classList.remove('highcharts-credits');
        e.innerHTML = "";
    });
    setInterval(function () {
        document.querySelectorAll('text').forEach( (e,index) => {
            let res = e.innerHTML ;
            //
            if(res.includes("Highcharts.com") ){
                e.classList.remove('.highcharts-credits');
                e.style.color= "blue";
                e.innerHTML = "";
                //console.log(res);
            }
        
        });
    }, 100);

    GainPercentContentHighcharts(TWRrate);
    SymbolsContentHighcharts(Symbols);
    AdvancedStatsHighcharts(AdvancedStats);
    HourlyHighcharts(HourlyTabel,xHourlyTabel,yHourlyTabel,zHourlyTabel);
    DailyTabelHighcharts(DailyTabel,xDailyTabel,yDailyTabel,zDailyTabel);
    TradeLengthHighcharts(AvgTradeLengthTabelProfit);

    
    MonthlyUSDContent(Profits,Profits_for_Monthly);
    MonthlyPercentContentHighcharts(TWRrate);

</script>




<!-- --------------------------------------------------------------------------------- --> 
<!-- --------------------------------------------------------------------------------- -->
<!-- ------------------                                      ------------------------- -->
<!-- ------------------     Update History {by Login}        ------------------------- -->
<!-- ------------------                                      ------------------------- -->
<!-- --------------------------------------------------------------------------------- -->
<!-- --------------------------------------------------------------------------------- -->


<div id="login" class="Login id" login="{{$id}}"> </div>

<script>

    setTimeout(UpdateHistory, 10);
    
    function UpdateHistory() {
        
       document.getElementById("UpdateHistory").submit();
    }
      
    </script>
    <?php     
            $Route  = 'gethistory';  
     ?>
    <form style="display:none "   name="UpdateHistory" id="UpdateHistory" class="UpdateHistory" target="UpdateHistoryiframe" 
     method="POST" action="{{ route($Route) }}">
      @csrf 
      <input type="text" name="login" value="{{$id}}" />
      <input type="submit" value="Skicka Tips" /> 
    </form>
    <iframe style="display:none " name="UpdateHistoryiframe" ><div id="login" class="Login id" login="{{$id}}"> </div></iframe>


    