<?php

namespace App\Filters;

use App\Filters\QueryFilter;

class UserFilters extends QueryFilter
{
    // localhost.com/users?name=asd&email=asd&status
    public function name($name)
    {
        return $this->builder->where('name', 'LIKE', '%' . $name . '%');
    }

    public function email($email)
    {
        return $this->builder->where('email', 'LIKE', '%' . $email . '%');
    }

    public function status()
    {
        return $this->builder->where('status', false);
    }
}
