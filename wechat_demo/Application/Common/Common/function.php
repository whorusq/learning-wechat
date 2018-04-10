<?php

/**
 * 统一使用 Gearman 请求 Java 接口
 * added by xxx
 *
 * @param  string $api     接口名称，格式 name.method_name
 * @param  array $data          接口请求参数
 * @param  function $callback 1，默认值，直接返回接口处理结果给调用处；
 *                            0，不返回值；
 *                            回调函数，用于需要对接口返回结果进行处理的情况
 *
 * @uses 以绑定公众号接口为例
 *
 *      $para['userid'] = '41501';
 *      $para['openid'] = 'sdfsdfsdfsd';
 *
 *      // 方式一，直接返回接口处理结果
 *      $ret = sys_gearman('user.update_weixinid', $para);
 *
 *      // 方式二，调用接口，不返回结果
 *      sys_gearman('user.update_weixinid', $para, 0);
 *
 *      // 方式三，使用匿名函数处理接口处理结果
 *      sys_gearman('user.update_weixinid', $para, function ($result) use (&$para) {
 *          var_dump($para); // 使用匿名函数作用域外部的变量
 *          var_dump($result); // 这里的 $result 是调用 sys_gearman 返回的传参
 *          $para['ok'] = 'yes'; // 修改匿名函数作用域外部的变量
 *       });
 *       var_dump($para);
 *
 * @return void
 */
function sys_gearman($api, $data = [], $callback = 1)
{

    if (!$api) {
        return ['status' => 414, 'message' => '缺少必传参数！'];
    }
    $api = explode('.', $api);
    $apiName = $api[0];
    $para['sessionId'] = session_id();
    $para['methodName'] = $api[1];
    $para['data'] = !empty($data) ? sys_filter_arr($data) : (object)null;

    // 请求接口，处理返回数据
    $gearmanServer = C('GEAMAN_SERVER_HOST');
    $gearmanPort = C('GEAMAN_SERVER_PORT');
    if (PHP_OS == 'Linux') {
        $client = new \GearmanClient();
        $client->addServer($gearmanServer, $gearmanPort);
        if ($callback) {
            $result = $client->do($apiName, json_encode($para));
            $finalResult = ini_get('magic_quotes_gpc') == '1' ? stripslashes($result) : json_decode($result, true);
            if (is_callable($callback)) {
                call_user_func($callback, $finalResult);
            } else {
                return $finalResult;
            }
        } else {
            $client->doBackground($apiName, json_encode($para));
        }
    } else {
        $client = new \Net_Gearman_Client(implode(':', [$gearmanServer, $gearmanPort]));
        $set = new \Net_Gearman_Set();
        $task = new \Net_Gearman_Task($apiName, json_encode($para));
        $set->addTask($task);
        $client->runSet($set);
        if ($callback) {
            if (is_callable($callback)) {
                call_user_func($callback, $task->result);
            } else {
                return $task->result;
            }
        }
    }
}

// ....
