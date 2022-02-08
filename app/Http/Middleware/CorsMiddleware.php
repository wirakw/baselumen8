<?php
namespace App\Http\Middleware;

use Closure;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $headers = [
            'Access-Control-Allow-Origin'      => '*',
            'Access-Control-Allow-Methods'     => 'GET, POST, OPTIONS, PUT, DELETE',
            //'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Max-Age'           => '86400',
            'Access-Control-Allow-Headers'     => 'Content-Type, Authorization, X-Requested-With, *',
            // 'X-Content-Type-Options'           => 'nosniff',
        ];

        if ($request->isMethod('OPTIONS'))
        {

            $headers = [
                'Access-Control-Allow-Origin'      => '*',
                'Access-Control-Allow-Methods'     => 'GET, POST, OPTIONS, PUT, DELETE',
               // 'Access-Control-Allow-Credentials' => 'true',
                'Access-Control-Max-Age'           => '86400',
                'Access-Control-Allow-Headers'     => 'Content-Type, Authorization, X-Requested-With, *',
            ];

            return response()->json(["method" => "OPTIONS"], 200, $headers)
                ->setCallback($request->input('callback'));
        }

        $response = $next($request);
        // $IlluminateResponse = 'Illuminate\Http\JsonResponse';
        $SymfonyResopnse = 'Symfony\Component\HttpFoundation\BinaryFileResponse';

        if($response instanceof $SymfonyResopnse) {
            foreach ($headers as $key => $value) {
                $response->headers->set($key, $value);
            }
        } else {
            foreach ($headers as $key => $value) {
                $response->header($key, $value);
            }
        }

        return $response;
    }
}
