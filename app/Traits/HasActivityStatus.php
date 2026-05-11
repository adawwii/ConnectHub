<?php

namespace App\Traits;

trait HasActivityStatus
{
     public function getFormattedLastSeenAttribute()
    {
        $date = $this->last_seen_at;
        if (!$date) return 'a long time ago';
        $now = now();
        if ($date->diffInMinutes($now) < 1) return 'Just now';
        
        if ($date->diffInMinutes($now) < 60) {
            return floor($date->diffInMinutes($now)) . ' minutes ago';
        }
        
        if ($date->isToday()) return $date->format('g:i A');
        
        if ($date->isYesterday()) return 'Yesterday';
        
        if ($date->diffInDays($now) < 7) {
            return 'Last seen on ' . $date->format('l');
        }
        
        if ($date->diffInDays($now) < 14) return 'Last week';
        
        return $date->format('M j, Y');
    }
}
