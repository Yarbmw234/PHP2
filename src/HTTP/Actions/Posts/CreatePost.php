<?php

namespace GeekBrains\LevelTwo\Http\Actions\Posts;

use GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\LevelTwo\Blog\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Blog\Post;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Blog\Exceptions\HttpException;
use GeekBrains\LevelTwo\HTTP\Actions\ActionInterface;
use GeekBrains\LevelTwo\HTTP\ErrorResponse;
use GeekBrains\LevelTwo\HTTP\Request;
use GeekBrains\LevelTwo\HTTP\Response;
use GeekBrains\LevelTwo\HTTP\SuccessfulResponse;

class CreatePost implements ActionInterface
{
  public function __construct(
    private UsersRepositoryInterface $usersRepository,
    private PostsRepositoryInterface $postsRepository
  ) {
  }

  public function handle(Request $request): Response
  {
    try {
      $authorUuid = new UUID($request->jsonBodyField('author_uuid'));
    } catch (HttpException | InvalidArgumentException $e) {
      return new ErrorResponse($e->getMessage());
    }

    try {
      $user = $this->usersRepository->get($authorUuid);
    } catch (UserNotFoundException $e) {
      return new ErrorResponse($e->getMessage());
    }

    $newPostUuid = UUID::random();

    try {
      $post = new Post(
        $newPostUuid,
        $user,
        $request->jsonBodyField('title'),
        $request->jsonBodyField('text'),
      );
    } catch (HttpException $e) {
      return new ErrorResponse($e->getMessage());
    }

    $this->postsRepository->save($post);

    return new SuccessfulResponse([
      'uuid' => (string)$newPostUuid,
    ]);
  }
}
