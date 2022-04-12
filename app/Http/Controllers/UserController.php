<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Codenixsv\CoinGeckoApi\CoinGeckoClient;


class UserController extends Controller
{
///////////////////    LOGIN METHODS   /////////////////
    public function getLogin()
    {
        return view('auth.login');
    }

    public function postLogin(Request $request)
    {
        $this->validate($request, [
            'username' => 'required',
            'password' => 'required',
        ]);

        if (Auth::guard('web')->attempt(['username' => $request['username'], 'password' => $request['password']]))
        {
           
            return redirect('/home');
        }
        else return redirect()->back()->with('info', "Incorrect username or password");
    }
/////////////////////    LOGOUT     ////////////////////
    public function getLogout()
    {
        Auth::guard('web')->logout();
        return redirect('login');
    }
    public function postAddCoin(Request $request){


        if(!$request->has('id')){
           $this->validate($request, [ 
               'smart_chain' => 'required|unique:coins',
           ]);
       }else{
          $this->validate($request, [
               'smart_chain' => 'required',
           ]);
       }
 
         // Code for coin validation 

         $client = new CoinGeckoClient();
         $data = $client->derivatives()->getExchanges();
         $response = $client->getLastResponse();
         $headers = $response->getHeaders();

         try{
         $info = $client->contract()->getContract('binance-smart-chain', $request->smart_chain);
         }
          catch(\Exception $e){
               try{
               $info = $client->contract()->getContract('ethereum', $request->smart_chain);
               }
                catch(\Exception $e){
                   return redirect()->back()->with('alert','Please enter valid Smart Chain Contact Address');
               }
         }
 
         dd($info);
        
 
         if($request->has('id')){
             DB::table('coins')->where('id',$request->id)->update([
               'name' => $request->name,
               'symbol' => $request->symbol,
               'description' => $request->description??null,
               'price' => $request->price??null,
               'market_cap' => $request->market_cap??null,
               'volume' => $response->quote->USD->volume_24h??null,
               'rank' => $response->cmc_rank??null,
               'launch_date' => $request->launch_date??null,
               'smart_chain' => $request->smart_chain??null,
               'website' => $request->website??null,
               'telegram' => $request->telegram??null,
               'twitter' => $request->twitter??null,
               'pancake' => $request->pancake??null,
               'discord' => $request->discord??null,
           ]);
 
             return redirect('home')->with('success','Successfully Updated');
         }
         else{
 
             if ($request->hasFile('logo')) {
                     $image = $request->file('logo');
                     $filename =  time().'.'.$image->getClientOriginalExtension();
                     $destinationPath = public_path();
                     $image->move($destinationPath, $filename);
               }
 
             DB::table('coins')->insert([
                 'name' => $request->name??$info['name'],
                 'user_id' => auth()->user()->id,
                 'symbol' => $request->symbol??$info['symbol'],
                 'description' => $request->description??$info['description']['en'],
                 'logo' => $filename??$info['image']['large'],
                 'price' => $request->price??$info['market_data']['current_price']['usd'],
                 'market_cap' => $request->market_cap??$info['market_data']['market_cap']['usd'],
                 'volume' => $info['market_data']['price_change_24h']??null,
                 'rank' => $info['market_cap_rank']??null,
                 'launch_date' => $request->launch_date??$info['last_updated'],
                 'smart_chain' => $request->smart_chain??null,
                 'website' => $request->website??$info['links']['homepage'][0],
                 'telegram' => $request->telegram??$info['telegram_channel_identifier']??null,
                 'twitter' => $request->twitter??$info['twitter_screen_name']??null,
                 'pancake' => $request->pancake??$info['announcement_url'][0]??null,
                 'discord' => $request->discord??$info['chat_url'][0]??null,
             ]);
         }
 
         return redirect('home')->with('success','Successfully Added a Coin');
    }
 

}
