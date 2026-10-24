<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Memo;

class MemosController extends Controller
{

    public function index(){
      $memos = Memo::where('user_id', auth()->id())->orderBy('created_at', 'desc')->get();
      return view('index', ['memos' => $memos]);
    }


    public function create(){
      return view ('create');
    }

    public function store(Request $request)
    {
      $content = $request->validate(['content' => 'required|string|max:300']);
      $memo = new Memo($content);
      $memo->user_id = $request->user()->id;
      $memo->save();
      return redirect()->route('index');
    }

    public function edit(Request $request){
      $memo = Memo::where('user_id', $request->user()->id)->findOrFail($request->id);
      return view('edit', ['memo' => $memo,]);
    }

    public function update(Request $request)
    {
      $memo = Memo::where('user_id', $request->user()->id)->findOrFail($request->id);
      $content = $request->validate(['content' => 'required|string|max:300']);
      $memo->fill($content)->save();
      return redirect()->route('index');
    }

    public function delete(Request $request)
    {
      $memo = Memo::where('user_id', $request->user()->id)->findOrFail($request->id);
      $memo->delete();
      return redirect()->route('index');
    }
}
