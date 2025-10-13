import React from 'react';
import { Head, router } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { ArrowLeftIcon, CalendarIcon, UsersIcon, ClockIcon, ArchiveIcon, CheckCircleIcon } from 'lucide-react';
import { format } from 'date-fns';
import AdminLayout from '@/layouts/admin/layout';

interface InternshipSeason {
  id: number;
  name: string;
  start_date: string;
  end_date: string;
  status: 'active' | 'completed' | 'archived';
  created_at: string;
  updated_at: string;
}

interface Student {
  id: number;
  student_number: string;
  first_name: string;
  last_name: string;
  middle_name?: string;
  section: {
    section_name: string;
  };
  is_active: boolean;
  created_at: string;
}

interface SeasonStats {
  season: InternshipSeason;
  deadline_count: number;
  active_deadline_count: number;
  student_count: number;
  active_student_count: number;
  is_in_progress: boolean;
  has_ended: boolean;
  next_category?: string;
}

interface Props {
  season: InternshipSeason;
  stats: SeasonStats;
  archivedStudents: Student[];
}

export default function SeasonStats({ season, stats, archivedStudents }: Props) {
  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'active':
        return <Badge className="bg-green-100 text-green-800">🟢 Active</Badge>;
      case 'completed':
        return <Badge className="bg-blue-100 text-blue-800">✅ Completed</Badge>;
      case 'archived':
        return <Badge className="bg-gray-100 text-gray-800">📁 Archived</Badge>;
      default:
        return <Badge variant="secondary">{status}</Badge>;
    }
  };

  const formatDate = (dateString: string) => {
    return format(new Date(dateString), 'MMM dd, yyyy');
  };

  const formatDateTime = (dateString: string) => {
    return format(new Date(dateString), 'MMM dd, yyyy HH:mm');
  };

  return (
    <AdminLayout>
      <Head title={`${season.name} - Statistics`} />
      
      <div className="p-4 md:p-6 space-y-6">
        {/* Header */}
        <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div className="flex items-center gap-4">
            <Button
              variant="outline"
              size="sm"
              onClick={() => router.get('/admin/seasons')}
              className="flex items-center gap-2"
            >
              <ArrowLeftIcon className="h-4 w-4" />
              Back to Seasons
            </Button>
            <div>
              <h1 className="text-2xl font-bold text-gray-900">{season.name}</h1>
              <p className="text-gray-600">Season Statistics & Overview</p>
            </div>
          </div>
          {getStatusBadge(season.status)}
        </div>

        {/* Season Overview */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <CalendarIcon className="h-5 w-5" />
              Season Overview
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
              <div className="space-y-2">
                <p className="text-sm font-medium text-gray-500">Start Date</p>
                <p className="text-lg font-semibold">{formatDateTime(season.start_date)}</p>
              </div>
              <div className="space-y-2">
                <p className="text-sm font-medium text-gray-500">End Date</p>
                <p className="text-lg font-semibold">{formatDateTime(season.end_date)}</p>
              </div>
              <div className="space-y-2">
                <p className="text-sm font-medium text-gray-500">Duration</p>
                <p className="text-lg font-semibold">
                  {Math.ceil((new Date(season.end_date).getTime() - new Date(season.start_date).getTime()) / (1000 * 60 * 60 * 24))} days
                </p>
              </div>
              <div className="space-y-2">
                <p className="text-sm font-medium text-gray-500">Status</p>
                <div className="flex items-center gap-2">
                  {stats.is_in_progress && <ClockIcon className="h-4 w-4 text-blue-500" />}
                  {stats.has_ended && <CheckCircleIcon className="h-4 w-4 text-green-500" />}
                  <span className="text-lg font-semibold">
                    {stats.is_in_progress ? 'In Progress' : stats.has_ended ? 'Ended' : 'Upcoming'}
                  </span>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Statistics Cards */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          {/* Deadlines */}
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Deadlines</CardTitle>
              <CalendarIcon className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{stats.deadline_count}</div>
              <p className="text-xs text-muted-foreground">
                {stats.active_deadline_count} currently active
              </p>
            </CardContent>
          </Card>

          {/* Students */}
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Students</CardTitle>
              <UsersIcon className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{stats.student_count}</div>
              <p className="text-xs text-muted-foreground">
                {stats.active_student_count} currently active
              </p>
            </CardContent>
          </Card>

          {/* Archived Students */}
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Archived Students</CardTitle>
              <ArchiveIcon className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{archivedStudents.length}</div>
              <p className="text-xs text-muted-foreground">
                {stats.student_count - stats.active_student_count} total archived
              </p>
            </CardContent>
          </Card>

          {/* Next Category */}
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Next Category</CardTitle>
              <ClockIcon className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">
                {stats.next_category ? 'Available' : 'Complete'}
              </div>
              <p className="text-xs text-muted-foreground">
                {stats.next_category || 'All categories created'}
              </p>
            </CardContent>
          </Card>
        </div>

        {/* Archived Students List */}
        {archivedStudents.length > 0 && (
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <ArchiveIcon className="h-5 w-5" />
                Archived Students ({archivedStudents.length})
              </CardTitle>
              <CardDescription>
                Students who have been archived for this season
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {/* Mobile Cards */}
                <div className="block md:hidden space-y-3">
                  {archivedStudents.map((student) => (
                    <Card key={student.id} className="p-4">
                      <div className="space-y-2">
                        <div className="flex items-center justify-between">
                          <h4 className="font-medium">
                            {student.last_name}, {student.first_name}
                            {student.middle_name && ` ${student.middle_name}`}
                          </h4>
                          <Badge variant="secondary" className="text-xs">
                            Archived
                          </Badge>
                        </div>
                        <div className="text-sm text-gray-600 space-y-1">
                          <p>Student Number: {student.student_number}</p>
                          <p>Section: {student.section.section_name}</p>
                          <p>Archived: {formatDate(student.created_at)}</p>
                        </div>
                      </div>
                    </Card>
                  ))}
                </div>

                {/* Desktop Table */}
                <div className="hidden md:block overflow-x-auto">
                  <table className="w-full text-sm">
                    <thead>
                      <tr className="border-b">
                        <th className="text-left py-3 px-4 font-medium">Student Name</th>
                        <th className="text-left py-3 px-4 font-medium">Student Number</th>
                        <th className="text-left py-3 px-4 font-medium">Section</th>
                        <th className="text-left py-3 px-4 font-medium">Archived Date</th>
                        <th className="text-left py-3 px-4 font-medium">Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      {archivedStudents.map((student) => (
                        <tr key={student.id} className="border-b">
                          <td className="py-3 px-4">
                            {student.last_name}, {student.first_name}
                            {student.middle_name && ` ${student.middle_name}`}
                          </td>
                          <td className="py-3 px-4 font-mono text-sm">
                            {student.student_number}
                          </td>
                          <td className="py-3 px-4">
                            {student.section.section_name}
                          </td>
                          <td className="py-3 px-4 text-gray-600">
                            {formatDate(student.created_at)}
                          </td>
                          <td className="py-3 px-4">
                            <Badge variant="secondary" className="text-xs">
                              Archived
                            </Badge>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>

                {/* Empty State */}
                {archivedStudents.length === 0 && (
                  <div className="text-center py-8 text-gray-500">
                    <ArchiveIcon className="h-12 w-12 mx-auto mb-4 text-gray-300" />
                    <p>No archived students for this season</p>
                  </div>
                )}
              </div>
            </CardContent>
          </Card>
        )}

        {/* Actions */}
        <div className="flex flex-col sm:flex-row gap-4">
          <Button
            variant="outline"
            onClick={() => router.get(`/admin/seasons/${season.id}/archived-students`)}
            className="flex items-center gap-2"
          >
            <ArchiveIcon className="h-4 w-4" />
            View All Archived Students
          </Button>
          
          {season.status === 'completed' && (
            <Button
              variant="destructive"
              onClick={() => {
                if (confirm('Are you sure you want to archive all students in this season? This action cannot be undone.')) {
                  router.post(`/admin/seasons/${season.id}/archive-students`);
                }
              }}
              className="flex items-center gap-2"
            >
              <ArchiveIcon className="h-4 w-4" />
              Archive All Students
            </Button>
          )}
        </div>
      </div>
    </AdminLayout>
  );
}
