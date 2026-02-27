@php
    /**
     * Build a hierarchical tree for D3.js
     * Structure: Root (User) -> Parents -> Grandparents
     */
    $buildNode = function ($u, $isUser = false) {
        if (!$u)
            return null;
        return [
            'id' => $u->id,
            'name' => $u->name,
            'gender' => $u->gender,
            'is_deceased' => $u->trashed(),
            'civil_status' => $u->civil_status,
            'is_user' => (bool)$isUser,
        ];
    };

    $treeData = $buildNode($user, true);

    // Add Parents
    $treeData['children'] = [];

    if ($user->father) {
        $fatherNode = $buildNode($user->father, false);
        $fatherNode['role'] = 'Father';
        $fatherNode['children'] = [];

        if ($user->father->father) {
            $pgf = $buildNode($user->father->father, false);
            $pgf['role'] = 'Grandfather';
            $fatherNode['children'][] = $pgf;
        }
        if ($user->father->mother) {
            $pgm = $buildNode($user->father->mother, false);
            $pgm['role'] = 'Grandmother';
            $fatherNode['children'][] = $pgm;
        }
        $treeData['children'][] = $fatherNode;
    }

    if ($user->mother) {
        $motherNode = $buildNode($user->mother, false);
        $motherNode['role'] = 'Mother';
        $motherNode['children'] = [];

        if ($user->mother->father) {
            $mgf = $buildNode($user->mother->father, false);
            $mgf['role'] = 'Grandfather';
            $motherNode['children'][] = $mgf;
        }
        if ($user->mother->mother) {
            $mgm = $buildNode($user->mother->mother, false);
            $mgm['role'] = 'Grandmother';
            $motherNode['children'][] = $mgm;
        }
        $treeData['children'][] = $motherNode;
    }

    // Siblings
    $siblings = collect();
    if ($user->father_id || $user->mother_id) {
        $siblings = \App\Models\User::withTrashed()
            ->where('id', '!=', $user->id)
            ->where(function ($q) use ($user) {
                if ($user->father_id)
                    $q->where('father_id', $user->father_id);
                if ($user->mother_id)
                    $q->orWhere('mother_id', $user->mother_id);
            })
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'gender' => $s->gender,
                'is_deceased' => $s->trashed(),
                'civil_status' => 'Sibling'
            ]);
    }
@endphp

<div x-data="lineageD3" x-init="initD3()" class="lineage-container">
    <script type="application/json" class="tree-data">@json($treeData)</script>
    <script type="application/json" class="siblings-data">@json($siblings)</script>

    <div id="d3-tree-wrapper" class="d3-wrapper" @resize.window="debouncedRender()">
        <svg id="lineage-svg"></svg>
    </div>

    @if(!$user->father && !$user->mother && $siblings->isEmpty())
        <div class="no-data">
            <svg xmlns="http://www.w3.org/2000/svg" class="no-data-icon" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
            </svg>
            <p>No lineage information available.</p>
        </div>
    @endif
</div>

<style>
    .lineage-container {
        width: 100%;
        height: 75vh;
        overflow: hidden;
        background: #f8fafc;
        border-radius: 1rem;
        position: relative;
    }

    .d3-wrapper {
        width: 100%;
        height: 100%;
    }

    #lineage-svg {
        width: 100%;
        height: 100%;
        cursor: grab;
    }

    #lineage-svg:active {
        cursor: grabbing;
    }

    .node-rect {
        stroke-width: 2px;
        rx: 12;
        ry: 12;
        transition: all 0.3s ease;
    }

    .node-text-name {
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 14px;
        fill: #1e293b;
    }

    .node-text-meta {
        font-family: 'Outfit', sans-serif;
        font-size: 11px;
        fill: #64748b;
    }

    .node-male .node-rect {
        fill: #eff6ff;
        stroke: #93c5fd;
    }

    .node-female .node-rect {
        fill: #fdf2f8;
        stroke: #f9a8d4;
    }

    .node-deceased .node-rect {
        fill: #fef2f2;
        stroke: #fca5a5;
        opacity: 0.8;
    }

    .node-deceased .node-text-name {
        text-decoration: line-through;
        fill: #991b1b;
    }

    .node-current .node-rect {
        fill: #ecfdf5;
        stroke: #10b981;
        stroke-width: 4px;
        filter: drop-shadow(0 4px 6px rgba(16, 185, 129, 0.2));
    }

    .link {
        fill: none;
        stroke: #cbd5e1;
        stroke-width: 2px;
        transition: stroke 0.3s;
    }

    .link-deceased {
        stroke: #fca5a5;
        stroke-dasharray: 5, 5;
    }

    .no-data {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        color: #94a3b8;
    }

    .no-data-icon {
        width: 4rem;
        height: 4rem;
        margin: 0 auto 1rem;
        opacity: 0.3;
    }
</style>