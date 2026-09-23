<?php

use function Livewire\Volt\{state, mount};
use App\Models\QueueTicket;

state([
    'token' => null,
    'queue' => null,
]);

mount(function ($token) {
    $this->token =$token;
    $this->queue = QueueTicket::where('access_token',$token)->firstOrFail();
});

$refreshQueue = function () {
    if ($this->token) {
        $this->queue = QueueTicket::where('access_token',$this->token)->first();
    }
};

?>

<div class="bg-gray-100 flex items-center justify-center min-h-screen font-sans w-full" wire:poll.5s="refreshQueue">
    <div class="bg-white p-8 rounded-xl shadow-lg border border-gray-200 text-center max-w-md w-full">
        <h2 class="text-gray-500 font-semibold mb-2 uppercase tracking-wide">Queue Status</h2>
        
        <div class="my-6">
            <h1 class="text-6xl font-black text-gray-800 mb-2">{{ $queue->tracking_number ?? '---' }}</h1>
            <p class="text-xl text-gray-600">{{ $queue->name ?? '---' }}</p>
        </div>

        <div class="mt-8 mb-4">
            <div id="status-badge" data-status="{{ $queue->status }}">
                @if(($queue->status ?? '') === 'holding')
                    <div class="bg-yellow-50 text-yellow-800 px-6 py-4 rounded-lg font-bold border border-yellow-200">
                        🕒 Waiting in Line
                    </div>
                @elseif(($queue->status ?? '') === 'active')
                    <div class="bg-blue-50 text-blue-800 px-6 py-4 rounded-lg font-bold border border-blue-200">
                        📢 You are next in line!
                    </div>
                @elseif(($queue->status ?? '') === 'serving')
                    <div class="bg-green-50 text-green-800 px-6 py-4 rounded-lg font-bold border border-green-200 shadow-sm animate-pulse">
                        ✅ Please proceed to {{ $queue->assigned_teller ?? 'the counter' }}
                    </div>
                @else
                    <div class="bg-gray-100 text-gray-800 px-6 py-4 rounded-lg font-bold">
                        Status: {{ ucfirst($queue->status ?? 'Unknown') }}
                    </div>
                @endif
            </div>
        </div>
        
        <p class="mt-6 text-sm text-gray-400 flex items-center justify-center gap-2">
            <svg class="animate-spin h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Auto-updating status
        </p>

        <button id="notifyBtn" class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">
            Allow Notifications
        </button>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const notifyBtn = document.getElementById("notifyBtn");
            const statusBadge = document.getElementById("status-badge");
            
            if (!statusBadge) return;
            
            let currentStatus = statusBadge.dataset.status;

            let activeNotificationCount = 0;
            const MAX_ACTIVE_NOTIFICATIONS = 5;
            let activeIntervalId = null;

            if (notifyBtn) {
                if ("Notification" in window && Notification.permission === "granted") {
                    notifyBtn.style.display = "none";
                }

                notifyBtn.addEventListener("click", () => {
                    if (!("Notification" in window)) {
                        alert("This browser does not support desktop notifications.");
                        return;
                    }

                    Notification.requestPermission().then(permission => {
                        if (permission === "granted") {
                            notifyBtn.style.display = "none";
                            new Notification("Notifications Allowed!", {
                                body: "You will be alerted when your status becomes active or serving."
                            });
                        } else {
                            alert("Notification permission was denied.");
                        }
                    });
                });
            }

            function startActiveNotifications() {
                sendActiveAlert();

                activeIntervalId = setInterval(() => {
                    if (activeNotificationCount < MAX_ACTIVE_NOTIFICATIONS && currentStatus === 'active') {
                        sendActiveAlert();
                    } else {
                        clearInterval(activeIntervalId);
                    }
                }, 60000);
            }

            function sendActiveAlert() {
                if ("Notification" in window && Notification.permission === "granted") {
                    activeNotificationCount++;
                    new Notification(`Reminder: You are Next! (${activeNotificationCount}/${MAX_ACTIVE_NOTIFICATIONS})`, {
                        body: "Your turn is coming up soon. Please get ready!",
                        requireInteraction: true
                    });
                }
            }

            function triggerServingNotification() {
                if ("Notification" in window && Notification.permission === "granted") {
                    new Notification("It's Your Turn Now! ✅", {
                        body: "Please proceed to the assigned counter immediately.",
                        requireInteraction: true
                    });
                }
            }

            if (currentStatus === 'active') {
                startActiveNotifications();
            }

            const observer = new MutationObserver(() => {
                const updatedBadge = document.getElementById('status-badge');
                if (!updatedBadge) return;

                const newStatus = updatedBadge.dataset.status;

                if (currentStatus !== newStatus) {
                    if (newStatus === 'active') {
                        activeNotificationCount = 0;
                        startActiveNotifications();
                    } else if (newStatus === 'serving') {
                        clearInterval(activeIntervalId);
                        triggerServingNotification();
                    } else {
                        clearInterval(activeIntervalId);
                    }

                    currentStatus = newStatus;
                }
            });

            observer.observe(statusBadge, { attributes: true, childList: true, subtree: true });
        });
    </script>
</div>