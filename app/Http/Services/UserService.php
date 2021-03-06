<?php

namespace App\Http\Services;

use App\Exceptions\UserNotFoundException;
use App\Http\Repositories\UserRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserService 
{
    private UserRepository $userRepository;
    
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Find an user by document
     * 
     * @param string $document -> document value of user
     * @return User
     */
    public function findByDocument(string $document)
    {
        $correlationId = Str::uuid()->toString();

        try {
            Log::channel('stderr')->info($correlationId . ' -> Finding user by document: ' . $document);

            $user = $this->userRepository->findByDocument($document);

            if(!$user) throw new UserNotFoundException($document);

            Log::channel('stderr')->info($correlationId . ' -> User ' . $document . ' found');

            return $user;
        } catch (\Exception $ex) {
            Log::channel('stderr')->error($correlationId . ' -> Error: ' . $ex);

            throw $ex;
        }
    }

    /**
     * Find an user by id
     * 
     * @param string $id -> user id
     * @return User
     */
    public function findById(string $id){
        $correlationId = Str::uuid()->toString();

        Log::channel('stderr')->info($correlationId . ' -> Finding user id: ' . $id);

        try {
            $user = $this->userRepository->findById($id);

            if(!$user) throw new UserNotFoundException($id);

            Log::channel('stderr')->info($correlationId . ' -> User ' . $id . ' found');

            return $user;
        } catch (\Exception $ex) {
            Log::channel('stderr')->error($correlationId . ' -> Error: ' . $ex);
              
            throw $ex;
        }
    }
}