<?php

namespace App\Http\Controllers;

use App\Models\TradingPlan;
use App\Services\TradingInsightsService;
use Illuminate\Http\Request;

class TradingPlanController extends Controller
{
    public function index(Request $request)
    {
        $user    = $request->user();
        $plan    = $user->tradingPlan ?? new TradingPlan(['user_id' => $user->id]);
        $service = new TradingInsightsService($user);
        $insights = $service->getInsights();

        return view('plan.index', compact('plan', 'insights'));
    }

    public function save(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'max_daily_loss'           => 'required|numeric|min:0',
            'max_weekly_loss'          => 'required|numeric|min:0',
            'max_position_size_pct'    => 'required|numeric|min:0|max:100',
            'max_trades_per_day'       => 'required|integer|min:1|max:50',
            'rules'                    => 'nullable|string',
            'trading_schedule'         => 'nullable|string',
            'market_conditions_allowed'=> 'nullable|string',
            'checklist_raw'            => 'nullable|string',
            'setups_raw'               => 'nullable|string',
        ]);

        // Parsear checklist
        if (!empty($data['checklist_raw'])) {
            $data['pre_trade_checklist'] = array_filter(
                array_map('trim', explode("\n", $data['checklist_raw']))
            );
        }
        // Parsear setups
        if (!empty($data['setups_raw'])) {
            $data['allowed_setups'] = array_filter(
                array_map('trim', explode(',', $data['setups_raw']))
            );
        }
        unset($data['checklist_raw'], $data['setups_raw']);
        $data['user_id'] = $user->id;

        TradingPlan::updateOrCreate(['user_id' => $user->id], $data);

        return redirect()->route('plan.index')->with('success', 'Plan de trading guardado.');
    }
}
