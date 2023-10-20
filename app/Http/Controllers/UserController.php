<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class UserController extends Controller
{
    public function postLogin(Request $request)
    {
        $validate = $request->validate([
            "name" => "required",
            "password" => "required",
        ]);

        if (!Auth::attempt($validate)) return redirect()->back();

        if (Auth::user()->roles_id == 1) return redirect("/admin");

        return redirect("/");
    }

    public function getLogin()
    {
        return view("login");
    }

    public function registerUser(Request $request)
    {
        User::create([
            "name" => $request->name,
            "password" => bcrypt($request->password)
        ]);

        return redirect("/login");
    }

    public function getRegisterUser()
    {
        return view("register");
    }

    public function index()
    {
        if (!Auth::user()) return view("dashboard");

        $transactionsKeranjang = Transaction::with("products")->where("users_id", Auth::user()->id)->where("status", "dikeranjang")->get();
        $transactionsBayar = Transaction::with("products")->where("users_id", Auth::user()->id)->where("status", "dibayar")->get();
        $wallets = Wallet::with("user")->get();
        $wallet_count = Wallet::with("user")->where("status", "selesai")->count();
        $wallet_bank = Wallet::with("user")->where("status", "selesai")->get();
        $user = Auth::user();
        $users = User::with("roles")->get();
        $nasabah = User::where("roles_id", "4")->count();
        $products = Product::with("transaction")->get();
        $wallet = Wallet::where("users_id", Auth::user()->id)->where("status", "selesai")->get();
        $creditTotal = $wallet->sum('credit');
        $debitTotal = $wallet->sum('debit');
        $credit_bank = $wallet_bank->sum('credit');
        $debit_bank = $wallet_bank->sum('debit');
        $difference = $creditTotal - $debitTotal;
        $difference_bank = $credit_bank - $debit_bank;

        if (Auth::user()->roles_id == 1) return view("admin", compact("user", "wallet", "difference", "products", "transactionsKeranjang", "transactionsBayar", "users"));
        if (Auth::user()->roles_id == 2) return view("kantin", compact("user", "wallet", "difference", "products", "transactionsKeranjang", "transactionsBayar"));
        if (Auth::user()->roles_id == 3) return view("bank", compact("wallets", "difference_bank", "nasabah", "wallet_count"));


        return view("home", compact("user", "wallet", "difference", "products", "transactionsKeranjang", "transactionsBayar"));
    }

    public function logout()
    {
        Session::flush();
        Auth::user();
        return view("dashboard");
    }
}