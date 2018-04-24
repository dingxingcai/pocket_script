<?php

//此项作为调试多个sheet用,
return array(
    'sqls' => array(
        '租赁用户' => array(
            'sql' => 'SELECT id,customerId,createdAt AS "创建时间" FROM `order` LIMIT 10',
            'database' => 'zulin',
            'step' => 10000
//                'pre_sql' => null
        ),
        '租赁订单' => array(
            'sql' => 'SELECT id,createdAt AS "创建时间",phone FROM `user`',
            'database' => 'zulin',
            'step' => 10000,
//                'pre_sql' => null
        )
    ),
    'to' => array('277309623@qq.com'),
    'cc' => array('277309623@qq.com'),
    'content' => 'Done.',   //邮件内容
    'subject' => '多sheet测试',    //邮件主题
    'mail_tpl' => 'default',    //邮件模板内容
    'preview_count' => 100  //预览的数目
);

