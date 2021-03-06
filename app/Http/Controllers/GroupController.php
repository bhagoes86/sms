<?php namespace sms\Http\Controllers;

use sms\Http\Requests;
use sms\Http\Controllers\Controller;

use Illuminate\Http\Request;

use sms\Group;

class GroupController extends Controller {

	function __construct() {
		$this->middleware('auth');
	}

	public function index()
	{
		if(\Request::ajax()) 
		{
			$term = \Input::get('term');
			$db = Group::select('pbk_groups.*', \DB::raw('count(pbk.ID) as count'))
						->leftJoin('pbk', 'pbk.GroupID', '=', 'pbk_groups.ID')
						->where('pbk_groups.Name','like','%'.$term.'%')
						->groupBy('pbk_groups.ID')
						->orderBy('pbk_groups.Name','asc')
						->get();
			return \Response::json($db);
		}
		else
		{
			return view('group.index');
		}
	}

	public function store()
	{
		$rules = ['name' => 'required|unique:pbk_groups'];
		$validator = \Validator::make(\Input::all(), $rules);
		if($validator->fails()){
			return \Response::json(['message'=>$validator]);
		}
		$db = Group::create(['Name'=>\Input::get('name')]);
		return \Response::json(['id'=>$db->ID]);
	}

	public function show($id)
	{
		if(\Request::ajax()) 
		{
			/* autocomplete response (New contact form, group field) */
			if($id==0){ 
				$term = \Input::get('term');
				$db = Group::where('Name','like',$term.'%')->get();
				$group = [];
				foreach ($db as $key) {
					$group[$key->ID] = ['label'=>$key->Name, 'id'=>$key->ID];
				}
				return \Response::json($group);
			}
			/* Detail group response */
			else 
			{
				$data = Group::select('pbk_groups.*', 'pbk.Name as cname', 'pbk.Number')->leftJoin('pbk', 'pbk.GroupID', '=', 'pbk_groups.ID')->where('pbk_groups.ID',$id)->get();
				if($data){
					return \Response::json($data);
				}else{
					return \Response::json(null, 404);
				}
			}
		}
		else
		{
			abort(404);
		}
	}

	public function update($id)
	{
		$db = Group::find($id);
		$db->Name = \Input::get('name');
		$db->save();
		return \Response::json(['id'=>$db->ID]);
	}

	public function destroy($id)
	{
		if(\Request::ajax()) 
		{
			$ids = explode(',', $id);
			$db = \DB::table('pbk_groups')->whereIn('ID', $ids)->delete();
			return $db;	
		}
	}

}
