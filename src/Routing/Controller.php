<?php

namespace TarBlog\Routing;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class Controller
{
    /**
     * 验证表单
     *
     * @param Request $request
     * @param $rules
     * @param mixed ...$params
     * @return array
     */
    protected function validate(Request $request, $rules, ...$params)
    {
        $validator = Validator::make($request->all(),$rules,...$params);

        return $validator->validate();
    }
}