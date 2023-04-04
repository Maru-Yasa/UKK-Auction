<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\Bid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ItemNotFoundException;

class BidController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Bid $bid)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bid $bid)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Bid $bid)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bid $bid)
    {
        //
    }

    public function best_bid(Request $request)
    {
        if ($request->ajax()) {
            $data = Bid::all()->where('auction_id', $request->query('auction_id'));
            return response()->json($data);
        }
    }

    public function place_bid(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'auction_id' => 'required',
                'offer' => 'required|numeric'
            ]);
    
            if ($validator->fails()) {
                return response([
                    'status' => false,
                    'message' => 'An error occured, please check again',
                    'messages' => $validator->errors(),
                    'data' => []
                ]);
            }
    
            if ($request->query('auction_id')) {
                $auction_id = $request->query('auction_id');
                try {
                    $auction = Auction::all()->where('id', $auction_id)->firstOrFail();
    
                    // jika offer lebih kecil dari best offer
                    if ($auction->best_offer > $request->offer) {
                        return response([
                            'status' => false,
                            'message' => "Offer must higher than best offer",
                            'data' => []
                        ]);
                    }
    
                    $newBid = Bid::create([
                        'auction_id' => $auction_id,
                        'user_id' => auth()->user()->id,
                        'offer' => $request->offer
                    ]);

                    $auction->update([
                        'best_offer' => $newBid->offer,
                    ]);
    
                    return response([
                        'status' => true,
                        'message' => 'Success placing bid',
                        'data' => Auction::with(['item', 'user'])->where('id', $auction->id)->first()
                    ]);
    
                } catch (ItemNotFoundException $th) {
                    return response([
                        'status' => false,
                        'message' => $th->getMessage(),
                        'data' => []
                    ]);
                }
            }
        }
    }

}
