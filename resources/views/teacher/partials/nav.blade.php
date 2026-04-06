<nav class="flex flex-wrap items-center gap-2 text-sm mb-8">
    <a href="{{ route('teacher.dashboard') }}" class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50">Dashboard</a>
    <a href="{{ route('teacher.question-bank.index') }}" class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50">Question bank</a>
    <a href="{{ route('teacher.quizzes.index') }}" class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50">Quizzes</a>
    <a href="{{ route('teacher.assignments.index') }}" class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50">Assignments</a>
    <a href="{{ route('teacher.reports.performance') }}" class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50">Performance</a>
</nav>
