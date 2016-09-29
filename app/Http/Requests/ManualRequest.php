<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class ManualRequest extends Request
{
    /**
     * ユーザーがこのリクエストを送るにあたって認証を必要とするかどうかを決定
     *
     * @return bool
     */
    public function authorize()
    {
        return true;  //  認証機能なし
    }

    /**
     * リクエストに対して適用するバリデーションルール（入力チェック）
     *
     * @return array
     */
    public function rules()
    {
        return [
            //  ボタンのみなのでチェックなし
        ];
    }
}
