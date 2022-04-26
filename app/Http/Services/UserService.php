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
        $logId = Str::uuid();

        try {
            Log::channel('stderr')->info('Finding user by document: ' . $document, [$logId]);

            $user = $this->userRepository->findByDocument($document);

            if(!$user) throw new UserNotFoundException($document);

            Log::channel('stderr')->info('User ' . $document . ' found', [$logId]);

            return $user;
        } catch (\Exception $ex) {
            Log::channel('stderr')->error($ex, [$logId]);

            throw $ex;
        }
    }

    /**
     * @param string $id -> user id
     * @return User
     */
    public function findById(string $id){
      $logId = Str::uuid();

      Log::channel('stderr')->info('Finding user id: ' . $id, [$logId]);

      try {
          $user = $this->userRepository->findById($id);

          if(!$user) throw new UserNotFoundException($id);

          Log::channel('stderr')->info('User ' . $id . ' found', [$logId]);

          return $user;
      } catch (\Exception $ex) {
          Log::channel('stderr')->error($ex, [$logId]);
            
          throw $ex;
      }
  }
}