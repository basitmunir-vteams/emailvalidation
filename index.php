<?php
/**
 * @package  Email Validation API
 * @author   Muhammad Basit Munir <basit.munir@nxb.com.pk>
 *
 */

/**
 * Generic class autoloader.
 * 
 * @param string $class_name
 */
function autoLoadClass($class_name) {
    $directories = array(
        'classes/',
        'classes/controllers/',
    );
    foreach ($directories as $directory) {
        $filename = $directory . $class_name . '.php';
        if (is_file($filename)) {
            require($filename);
            break;
        }
    }
}

/**
 * Register autoloader functions.
 */
spl_autoload_register('autoLoadClass');

/**
 * Parse the incoming request.
 */
$request = new Request();
if (isset($_SERVER['PATH_INFO'])) {
    $request->url_elements = explode('/', trim($_SERVER['PATH_INFO'], '/'));
}

/**
 * Route the request.
 */
if (!empty($request->url_elements)) {
    
    $controller_name = ucfirst($request->url_elements[0]) . 'Controller';
    if (class_exists($controller_name)) {
        $controller = new $controller_name;
        
        $action_name = strtolower($request->url_elements[1]);
        $response_str = call_user_func_array(array($controller, $action_name), array($request));
    }
    else {
        header('HTTP/1.1 404 Not Found');
        $response_str = 'Unknown request: ' . $request->url_elements[0];
    }
}
else {
    $response_str = 'Unknown request';
}

/**
 * Send the response to the client.
 */
$response_obj = Response::create($response_str, $_SERVER['HTTP_ACCEPT']);
echo $response_obj->render();