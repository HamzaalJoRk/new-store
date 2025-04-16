<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\Front\OrderGameRequest;
use App\Models\Game;
use App\Models\Order;
use App\Models\Package;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class GameController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *layouts-scrollable
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function show($slug)
    {
        if(Auth::check()){
            if(Auth::user()->whats_app == null) {
                return redirect()->route('front.completeRegister');
            }
        }

        $game =Game::with('packages')->IsShow()->where('slug',$slug)->firstOrFail();


        return view('front.games.show')->with([
            'game'=>$game,
        ]);
    }
    public function Order (OrderGameRequest $request){

            DB::beginTransaction();
        try {
            $randomNumber = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $user_id=\auth()->id();
            $invoice_no='OR'.$randomNumber.$user_id;
            $game=Game::where('id',$request->game_id)->first();
            if($game->have_packages){
                $package=Package::where('id',$request->package_id)->first();
                $qty_item=$package->quantity;
                $price_item=$package->price;
                $final_total=$request->qty * $package->price;
            }else{
                $qty_item=$game->min_qty;
                $price_item=$game->price_qty;
                $final_total=$request->qty * $game->price_qty;
                
            }
            
            if(auth()->user()->user_balance < $final_total){
                DB::rollBack();
                return redirect()->back()->with(['error_m'=>__('translation.Your current balance is not enough, please top up and try again')]);
            }else{
                auth()->user()->increment('amount_orders',$final_total);
                auth()->user()->decrement('user_balance',$final_total);
            }

            $date=[
                'user_id'=>$user_id,
                'game_id'=>$request->game_id,
                'package_id'=>$request->package_id,
                'invoice_no'=>$invoice_no,
                'player_id'=>$request->playerid,
                'player_name'=>$request->playername,
                'qty'=>$request->qty,
                'qty_item'=>$qty_item,
                'price_item'=>$price_item,
                'final_total'=>$final_total,
                'details'=>$request->note,
            ];

            Order::create($date);

                DB::commit();
            return redirect()->back()->with('message', __('translation.store_order_done'));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::emergency('File: ' . $e->getFile() . 'Line: ' . $e->getLine() . 'Message: ' . $e->getMessage());
            return redirect()->back()->with(['error_m'=>__('translation.same_thing_error')]);

        }


    }
}
