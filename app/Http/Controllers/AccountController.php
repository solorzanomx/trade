<?php

namespace App\Http\Controllers;

use App\Models\AccountTransaction;
use App\Models\Trade;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $transactions = AccountTransaction::where('user_id', $user->id)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(20);

        // ── Totales ──────────────────────────────────────────────────────────
        $totalDeposits    = AccountTransaction::totalDeposits($user->id);
        $totalWithdrawals = AccountTransaction::totalWithdrawals($user->id);
        $netCapital       = $totalDeposits - $totalWithdrawals;

        // P&L neto real de trades cerrados
        $tradingNetPnl = (float) Trade::where('user_id', $user->id)
            ->where('status', 'closed')
            ->sum('net_p_l');

        // Saldo teórico = capital aportado + P&L trading
        $theoreticalBalance = $netCapital + $tradingNetPnl;

        // Saldo actual real (ingresado manualmente o del último ajuste)
        $lastAdjustment = AccountTransaction::where('user_id', $user->id)
            ->where('type', 'adjustment')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->first();

        // Si hay un ajuste reciente, lo usamos como base del saldo actual
        $currentBalance = $lastAdjustment ? (float) $lastAdjustment->amount : null;

        // Retorno sobre capital aportado
        $roi = $netCapital > 0 ? round(($tradingNetPnl / $netCapital) * 100, 2) : null;

        return view('account.index', compact(
            'transactions', 'totalDeposits', 'totalWithdrawals',
            'netCapital', 'tradingNetPnl', 'theoreticalBalance',
            'currentBalance', 'roi'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type'   => 'required|in:deposit,withdrawal,adjustment',
            'amount' => 'required|numeric|min:0.01',
            'date'   => 'required|date',
            'notes'  => 'nullable|string|max:255',
        ]);

        AccountTransaction::create([
            'user_id' => $request->user()->id,
            'type'    => $data['type'],
            'amount'  => $data['amount'],
            'date'    => $data['date'],
            'notes'   => $data['notes'] ?? null,
        ]);

        return redirect()->route('account.index')->with('success', 'Movimiento registrado.');
    }

    public function destroy(Request $request, AccountTransaction $transaction)
    {
        abort_if($transaction->user_id !== $request->user()->id, 403);
        $transaction->delete();
        return redirect()->route('account.index')->with('success', 'Movimiento eliminado.');
    }

    /**
     * Importa CashTransactions del XML de IBKR (depósitos y retiros).
     * Llama esto desde IBKRImportService si se quiere auto-importar.
     */
    public static function importFromXml(\SimpleXMLElement $xml, int $userId): array
    {
        $imported = 0;
        foreach ($xml->FlexStatements->FlexStatement as $statement) {
            if (!isset($statement->CashTransactions)) continue;
            foreach ($statement->CashTransactions->CashTransaction as $ct) {
                $attrs = (array) $ct->attributes();
                $a     = $attrs['@attributes'];

                $txType = strtolower($a['type'] ?? '');
                if (!in_array($txType, ['deposits & withdrawals', 'deposits &amp; withdrawals'])) continue;
                if (($a['currency'] ?? '') !== 'USD') continue;

                $amount   = (float)($a['amount'] ?? 0);
                $dateStr  = $a['reportDate'] ?? $a['settleDate'] ?? null;
                $ibkrRef  = $a['transactionID'] ?? null;

                if (!$dateStr || $amount == 0) continue;

                // Evitar duplicados
                if ($ibkrRef && AccountTransaction::where('user_id', $userId)->where('ibkr_ref', $ibkrRef)->exists()) continue;

                $type = $amount > 0 ? 'deposit' : 'withdrawal';
                $date = \Carbon\Carbon::createFromFormat('Ymd', $dateStr)->toDateString();

                AccountTransaction::create([
                    'user_id'  => $userId,
                    'type'     => $type,
                    'amount'   => abs($amount),
                    'date'     => $date,
                    'notes'    => 'Importado de IBKR',
                    'ibkr_ref' => $ibkrRef,
                ]);
                $imported++;
            }
        }
        return ['imported' => $imported];
    }
}
