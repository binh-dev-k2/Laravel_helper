<?php


function jsonResponse($code, $data = [])
{
    return response()->json(
        [
            'data' => $data,
            'code' => $code,
        ],
        200
    );
}
