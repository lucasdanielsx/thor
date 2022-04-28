<?php

namespace App\Http\Repositories;

use App\Models\User;

class UserRepository 
{
    /**
     * Find User by document
     * 
     * @param string $document -> user document
     * @return User
     */
    public function findByDocument(string $document)
    {
        return User::where('document', $document)->first();
    }

    /**
     * Find user by Id
     * 
     * @param string $id -> transaction id
     * @return User
     */
    public function findById(string $id) {
      return User::where('id', $id)->first();
  }
}