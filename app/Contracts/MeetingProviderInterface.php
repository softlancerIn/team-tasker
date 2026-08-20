<?php

namespace App\Contracts;

use App\Models\Meeting;

interface MeetingProviderInterface
{
    /**
     * Create/Configure room name or room credentials.
     *
     * @param Meeting $meeting
     * @return string
     */
    public function createRoom(Meeting $meeting): string;

    /**
     * Get join URL for the given meeting.
     *
     * @param Meeting $meeting
     * @return string
     */
    public function getJoinUrl(Meeting $meeting): string;

    /**
     * Start meeting.
     *
     * @param Meeting $meeting
     * @return void
     */
    public function startMeeting(Meeting $meeting): void;

    /**
     * End meeting.
     *
     * @param Meeting $meeting
     * @return void
     */
    public function endMeeting(Meeting $meeting): void;
}
