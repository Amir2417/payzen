<?php

use App\Constants\GlobalConst;
use App\Constants\PaymentGatewayConst;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('user_wallet_id')->nullable();
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->unsignedBigInteger('agent_wallet_id')->nullable();
            $table->unsignedBigInteger('merchant_id')->nullable();
            $table->unsignedBigInteger('merchant_wallet_id')->nullable();
            $table->unsignedBigInteger('sandbox_wallet_id')->nullable();
            $table->unsignedBigInteger("payment_gateway_currency_id")->nullable();
            $table->enum('user_type',[
                GlobalConst::USER,
                GlobalConst::ADMIN,
            ])->nullable()->comment("transaction creator");
            $table->enum("type",[
                PaymentGatewayConst::TYPEADDMONEY,
                PaymentGatewayConst::TYPEMONEYOUT,
                PaymentGatewayConst::TYPEWITHDRAW,
                PaymentGatewayConst::TYPECOMMISSION,
                PaymentGatewayConst::TYPEBONUS,
                PaymentGatewayConst::TYPEREFERBONUS,
                PaymentGatewayConst::TYPETRANSFERMONEY,
                PaymentGatewayConst::TYPEMONEYEXCHANGE,
                PaymentGatewayConst::TYPEADDSUBTRACTBALANCE,
                PaymentGatewayConst::BILLPAY,
                PaymentGatewayConst::MOBILETOPUP,
                PaymentGatewayConst::VIRTUALCARD,
                PaymentGatewayConst::CARDBUY,
                PaymentGatewayConst::CARDFUND,
                PaymentGatewayConst::SENDREMITTANCE,
                PaymentGatewayConst::TYPEMAKEPAYMENT,
                PaymentGatewayConst::MERCHANTPAYMENT
            ]);
            $table->string('request_currency')->comment("In add money user wallet currency, money transfer receiver currency")->nullable();
            $table->string("trx_id")->comment("Transaction ID");
            $table->decimal('request_amount', 28, 8)->default(0);
            $table->decimal('payable', 28, 8)->default(0);
            $table->decimal('receive_amount', 28, 8)->nullable()->comment('add money: user wallet balance, money transfer: receiver amount, money out: user receive amount using manual info');
            $table->enum('receiver_type',[
                GlobalConst::USER,
                GlobalConst::ADMIN,
            ])->nullable()->comment("Uses maybe money transfer, make payment");
            $table->unsignedBigInteger('receiver_id')->nullable()->comment("Uses maybe money transfer, make payment");
            $table->decimal('available_balance', 28, 8)->default(0);
            $table->string('payment_currency')->nullable()->comment('user payment currency (wallet/gateway)');
            $table->string("remark")->nullable();
            $table->text("details")->nullable();
            $table->text("info")->nullable();
            $table->text("reject_reason")->nullable();
            $table->tinyInteger("status")->default(0)->comment("0: Default, 1: Success, 2: Pending, 3: Hold, 4: Rejected");
            $table->enum("attribute",[
                PaymentGatewayConst::SEND,
                PaymentGatewayConst::RECEIVED,
            ]);
            $table->timestamps();


            $table->foreign("admin_id")->references("id")->on("admins")->onDelete("cascade")->onUpdate("cascade");
            $table->foreign("user_id")->references("id")->on("users")->onDelete("cascade")->onUpdate("cascade");
            $table->foreign("user_wallet_id")->references("id")->on("user_wallets")->onDelete("cascade")->onUpdate("cascade");
            $table->foreign("agent_id")->references("id")->on("agents")->onDelete("cascade")->onUpdate("cascade");
            $table->foreign("agent_wallet_id")->references("id")->on("agent_wallets")->onDelete("cascade")->onUpdate("cascade");
            $table->foreign("merchant_id")->references("id")->on("merchants")->onDelete("cascade")->onUpdate("cascade");
            $table->foreign("merchant_wallet_id")->references("id")->on("merchant_wallets")->onDelete("cascade")->onUpdate("cascade");
            $table->foreign("payment_gateway_currency_id")->references("id")->on("payment_gateway_currencies")->onDelete("cascade")->onUpdate("cascade");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};
