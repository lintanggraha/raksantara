<?php

namespace App\Http\Controllers;

use App\RiskTreatment;
use Illuminate\Http\Request;

class RiskTreatmentKanbanController extends Controller
{
    /**
     * Display the Kanban Board.
     */
    public function index()
    {
        // Fetch all treatments with their associated risks for context
        $treatments = RiskTreatment::with('risk')->get();

        // Group the treatments by status for the Kanban columns
        $kanbanData = [
            'Not Started' => $treatments->where('status', 'Not Started'),
            'In Progress' => $treatments->where('status', 'In Progress'),
            'Blocked' => $treatments->where('status', 'Blocked'),
            'Completed' => $treatments->where('status', 'Completed'),
        ];

        return view('treatments.kanban', compact('kanbanData'));
    }

    /**
     * Update the status of a specific treatment via AJAX drag-and-drop.
     */
    public function updateStatus(Request $request, RiskTreatment $treatment)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:Not Started,In Progress,Blocked,Completed',
        ]);

        $treatment->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'message' => 'Treatment status updated to ' . $validated['status']
        ]);
    }
}
