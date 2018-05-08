<?php

namespace App\Utility;

class EtlConstant
{
    const FETCH_ZULIN_ORDER = 'etl.zl.fetch_zulin_order';               //抓取租赁订单数据
    const FETCH_ZULIN_ORDER_GOODS = 'etl.zl.fetch_zulin_order_goods';   //抓取租赁订单商品
    const FETCH_ORDER_EXP = 'etl.zl.fetch_order_exp';                   //租赁订单拓展表(fact_order)


    const FETCH_GJP_ORDER = 'etl.gjp.fetch_gjp_order';                 //抓取管家婆订单
    const FETCH_GJP_REFUND_ORDER = 'etl.gjp.fetch_gjp_refund_order';   //抓取管家婆退货订单
    const FETCH_GOODS_GJP_ORDER = 'etl.gjp.fetch_goods_gjp_order';   //抓取管家婆订单商品

    const  FETCH_GOODS_FROM_GJP = 'etl.goods.fetch_goods_from_gjp';   //抓取管家婆的商品
    const  FETCH_GOODS_FROM_ZL = 'etl.goods.fetch_goods_from_zl';   //抓取租赁的商品信息

    const FETCH_SALER_FRON_GJP = 'etl.saler.fetch_saler_from_gjp';   //从管家婆抓取店员数据数据
    const FETCH_ZULIN_STUFF = 'etl.saler.fetch_zulin_stuff';          //从租赁系统中抓取店员信息

    const  FETCH_STORE_FRON_GJP = 'etl.store.fetch_store_from_gjp';   //从管家婆抓取仓库数据
    const  FETCH_STORE_FRON_ZL = 'etl.store.fetch_store_from_zl';    //从租赁抓取门店信息

    const FETCH_VIP_FROM_GJP = 'etl.vip.fetch_vip_from_gjp';   //从管家婆中抓取会员数据


    //新的抓取订单
    const FETCH_BILLINDEX_ORDER = 'order_center.gjp.fetch_billindex';    //抓取管家婆billindex订单数据
    const FETCH_RETAILBILL_ORDER = 'order_center.gjp.fetch_retailbill';  //抓取管家婆retailbill订单数据

    const FETCH_ZULINORDER = 'order_center.zl.fetch_zulinorder';      //抓取租赁order
    const FETCH_PAYMENTREFUND = 'order_center.zl.fetch_paymentrefund'; //抓取租赁paymentrefund
    const FETCH_ORDERACTIONHISTORY = 'order_center.zl.fetch_orderactionhistory'; //抓取orderactionhistory订单





}