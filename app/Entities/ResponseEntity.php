<?php

namespace App\Entities;

class ResponseEntity
{
    /**
     * ===============================
     * ERROR TYPE
     * ===============================
     */
    const ERROR_TYPE_VALIDATION      = "VALIDATION FAILED";
    const ERROR_TYPE_SERVICE         = "SERVICE ERROR";
    const ERROR_TYPE_UNAUTHENTICATED = "UNAUTHENTICATED";

    /**
     * ===============================
     * ERROR MESSAGE LOGIN
     * ===============================
     */
    const ERROR_MESSAGE_LOGIN =
        'Username atau Password salah';

    const ERROR_MESSAGE_USERNAME =
        'Username tidak ditemukan';

    const ERROR_MESSAGE_PASSWORD =
        'Password salah';

    const ERROR_MESSAGE_UNAUTH =
        'Session login telah berakhir';

    const ERROR_MESSAGE_NOT_FOUND =
        'Data tidak ditemukan';

    const DEFAULT_ERROR_MESSAGE =
        'Terjadi kesalahan pada sistem';

    /**
     * ===============================
     * SUCCESS MESSAGE
     * ===============================
     */
    const SUCCESS_MESSAGE =
        'Berhasil';

    const SUCCESS_MESSAGE_LOGIN =
        'Login berhasil';

    const SUCCESS_MESSAGE_LOGOUT =
        'Logout berhasil';

    const SUCCESS_MESSAGE_CREATED =
        'Data berhasil ditambahkan';

    const SUCCESS_MESSAGE_UPDATED =
        'Data berhasil diperbarui';

    const SUCCESS_MESSAGE_DELETED =
        'Data berhasil dihapus';

    /**
     * ===============================
     * HTTP STATUS
     * ===============================
     */
    const HTTP_SUCCESS = 200;

    const HTTP_SUCCESS_CREATED = 201;

    const HTTP_FAILED_PROCESS = 422;

    const HTTP_UNAUTHORIZE = 401;

    const HTTP_NOT_FOUND = 404;

    const HTTP_SERVER_ERROR = 500;

    /**
     * ===============================
     * DYNAMIC MESSAGE
     * ===============================
     */

    public static function getSuccessCreateMsg($name): string
    {
        return "$name berhasil ditambahkan";
    }

    public static function getSuccessUpdateMsg($name): string
    {
        return "$name berhasil diperbarui";
    }

    public static function getSuccessDeleteMsg($name): string
    {
        return "$name berhasil dihapus";
    }

    public static function getNotFoundMsg($name): string
    {
        return "$name tidak ditemukan";
    }
}