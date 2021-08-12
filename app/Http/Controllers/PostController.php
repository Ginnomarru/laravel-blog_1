<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostRequest;
use App\Post;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Str;
use Storage;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except('index', 'show');
    }


    public function index(Request $request)
    {
        if ($request->search) {
            $posts = Post::join('users', 'author_id', '=', 'users.id')
                ->where('title', 'like', '%'.$request->search.'%')
                ->orWhere('descr', 'like', '%'.$request->search.'%')
                ->orWhere('name', 'like', '%'.$request->search.'%')
                ->orderBy('posts.created_at', 'desc')
                ->get();
                return view('posts.index', compact('posts'));
        }

        $posts = Post::join('users', 'author_id', '=', 'users.id')
                ->orderBy('posts.created_at', 'desc')
                ->paginate(4);
        return view('posts.index', compact('posts'));
    }

    public function create()
    {
        return view('posts.create');
    }

    public function store(PostRequest $request)
    {
        $post = new Post();
        $post->title = $request->title;
        $post->short_title = Str::length($request->title)>30 ? Str::substr($request->title, 0, 30) . '...' : $request->title;
        $post->descr = $request->descr;
        $post->author_id = \Auth::user()->id;

        if($request->file('img')) {
            $path = Storage::putFile('public', $request->file('img'));
            $url = Storage::url($path);
            $post->img = $url;
        }

        $post->save();

        return redirect()->route('post.index')->with('success', 'Пост успешно создан');

    }

    public function show($id)
    {
        $post = Post::join('users', 'author_id', '=', 'users.id')
                ->find($id);

        if (!$post) {
            return redirect()->route('post.index')->withErrors('Ты куда-то не туда пытался зайти');
        }
        return view('posts.show', compact('post'));
    }

    public function edit($id)
    {
        $post = Post::find($id);
        if ($post->author_id != Auth::user()->id) {
            return redirect()->route('post.index')->withErrors('Вы не можете редактировать данный пост');
        }
        return view('posts.edit', compact('post'));
    }

    public function update(PostRequest $request, $id)
    {
        $post = Post::find($id);

        if ($post->author_id != Auth::user()->id) {
            return redirect()->route('post.index')->withErrors('Вы не можете редактировать данный пост');
        }
        $post->title = $request->title;
        $post->short_title = Str::length($request->title)>30 ? Str::substr($request->title, 0, 30) . '...' : $request->title;
        $post->descr = $request->descr;
        if($request->file('img')) {
            $path = Storage::putFile('public', $request->file('img'));
            $url = Storage::url($path);
            $post->img = $url;
        }
        $post->update();
        $id = $post->post_id;
        return redirect()->route('post.show', compact('id'))->with('success', 'Пост успешно изменен!');
    }

    public function destroy($id)
    {
        $post = Post::find($id);
        if ($post->author_id != Auth::user()->id) {
            return redirect()->route('post.index')->withErrors('Вы не можете редактировать данный пост');
        }
        $post->delete();
        return redirect()->route('post.index')->with('success', 'Пост успешно удален!');
    }
}
