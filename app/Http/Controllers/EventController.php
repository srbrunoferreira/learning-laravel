<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Event;
use App\Models\User;

class EventController extends Controller {
	public function index() {
		$search = request('search');

		if ($search) {
			$events = Event::where('title', 'like', '%' . $search . '%')->get();
		} else {
			$events = Event::all();
		}

		return view('welcome', ['events' => $events, 'search' => $search]);
	}

	public function create() {
		return view('event.create');
	}

	public function store(Request $request) {
		$event = new Event();

		$event->title = $request->title;
		$event->date = $request->date;
		$event->city = $request->city;
		$event->private = $request->private;
		$event->description = $request->description;
		$event->items = $request->items;

		if ($request->hasFile('image') && $request->file('image')->isValid()) {
			$requestImage = $request->image;

			$imgType = $requestImage->extension();

			$imgName = md5($requestImage->getClientOriginalName() . strtotime('now'));
			$imgName .= '.' . $imgType;

			$request->image->move(public_path('img/events'), $imgName);
			$event->image = $imgName;
		}

		$user = auth()->user();
		$event->user_id = $user->id;

		$event->save();

		return redirect('/')->with('msg', 'Evento criado com sucesso!');
	}

	public function show($id) {
		$event = Event::findOrFail($id);

		$user = auth()->user();
		$hasUserJoined = false;

		if ($user) {
			$userEvents = $user->eventAsParticipant->toArray();
			foreach ($userEvents as $userEvent) {
				if ($userEvent['id'] == $id) {
					$hasUserJoined = true;
				}
			}
		}

		$eventOwner = User::where('id', $event->user_id)->first()->toArray();

		return view('event.show', ['event' => $event, 'eventOwner' => $eventOwner, 'hasUserJoined' => $hasUserJoined]);
	}

	public function dashboard() {
		$user = auth()->user();

		$events = $user->events;

		$eventsAsParticipant = $user->eventAsParticipant;

		return view('event.dashboard', ['events' => $events, 'eventsAsParticipant' => $eventsAsParticipant]);
	}

	public function destroy($id) {
		Event::findOrFail($id)->delete();

		return redirect('/dashboard')->with('msg', 'Evento exlucído com sucesso!');
	}

	public function edit($id) {
		$event = Event::findOrFail($id);

		$user = auth()->user();

		if ($user->id != $event->user_id) {
			return redirect('/dashboard');
		}

		return view('event.edit', ['event' => $event]);
	}

	public function update(Request $request) {
		$data = $request->all();

		if ($request->hasFile('image') && $request->file('image')->isValid()) {
			$requestImage = $request->image;

			$imgType = $requestImage->extension();

			$imgName = md5($requestImage->getClientOriginalName() . strtotime('now'));
			$imgName .= '.' . $imgType;

			$request->image->move(public_path('img/events'), $imgName);
			$data['image'] = $imgName;
		}

		Event::findOrFail($request->id)->update($data);

		return redirect('/dashboard')->with('msg', 'Evento editado com sucesso!');
	}

	public function joinEvent($id) {
		$user = auth()->user();

		$user->eventAsParticipant()->attach($id);

		$event = Event::findOrFail($id);

		return redirect('/dashboard')->with('msg', 'Sua presença está confirmada no evento ' . $event->title);
	}

	public function deleteEvent($id) {
		$user = auth()->user();

		$user->eventAsParticipant()->detach($id);

		$event = Event::findOrFail($id);

		return redirect('/dashboard')->with('msg', 'Voc~e saiu com sucesso do evento ' . $event->title);
	}
}
