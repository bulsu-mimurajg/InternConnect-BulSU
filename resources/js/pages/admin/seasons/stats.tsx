import React from 'react';
import { Head, router } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { AlertTriangleIcon, ArrowLeftIcon, CalendarIcon, UsersIcon, ClockIcon, ArchiveIcon, CheckCircleIcon } from 'lucide-react';
import { format } from 'date-fns';
import AdminLayout from '@/layouts/admin/layout';

interface InternshipSeason {
  id: number;
  name: string;
  start_date: string;
  end_date: string;
  status: 'active' | 'inactive' | 'completed' | 'archived';
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

interface PlacedStudent {
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
  placements: {
    id: number;
    status: string;
    compatibility_score: number;
    placement_date: string;
    internship: {
      id: number;
      company_name: string;
      position_title: string;
    };
  }[];
}

interface UnplacedStudent {
  id: number;
  student_id: number;
  reason: string;
  requires_manual_intervention: boolean;
  created_at: string;
  student: Student;
}

interface Props {
  season: InternshipSeason;
  stats: SeasonStats;
  archivedStudents: Student[];
  placedStudents: PlacedStudent[];
  unplacedStudents: UnplacedStudent[];
}

export default function SeasonStats({ season, stats, archivedStudents, placedStudents, unplacedStudents }: Props) {
  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'active':
        return <Badge className="bg-green-100 text-green-800">🟢 Active</Badge>;
      case 'inactive':
        return <Badge className="bg-gray-100 text-gray-800">⚪ Inactive</Badge>;
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

          {/* Placed Students */}
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Placed Students</CardTitle>
              <CheckCircleIcon className="h-4 w-4 text-green-600" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-green-600">{placedStudents.length}</div>
              <p className="text-xs text-muted-foreground">
                Successfully placed
              </p>
            </CardContent>
          </Card>

          {/* Unplaced Students */}
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Unplaced Students</CardTitle>
              <AlertTriangleIcon className="h-4 w-4 text-destructive" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold text-destructive">{unplacedStudents.length}</div>
              <p className="text-xs text-muted-foreground">
                Require manual placement
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

        {/* Placed Students List */}
        {placedStudents.length > 0 && (
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <CheckCircleIcon className="h-5 w-5 text-green-600" />
                Placed Students ({placedStudents.length})
              </CardTitle>
              <CardDescription>
                Students who have been successfully placed in internships for this season
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {/* Mobile Cards */}
                <div className="block md:hidden space-y-3">
                  {placedStudents.map((student) => {
                    const approvedPlacement = student.placements.find(p => p.status === 'approved');
                    return (
                      <Card key={student.id} className="p-4 border-green-200 bg-green-50">
                        <div className="space-y-2">
                          <div className="flex items-center justify-between">
                            <h4 className="font-medium">
                              {student.last_name}, {student.first_name}
                              {student.middle_name && ` ${student.middle_name}`}
                            </h4>
                            <Badge className="bg-green-100 text-green-800 text-xs">
                              Placed
                            </Badge>
                          </div>
                          <div className="text-sm text-gray-600 space-y-1">
                            <p>Student Number: {student.student_number}</p>
                            <p>Section: {student.section.section_name}</p>
                            {approvedPlacement && (
                              <>
                                <p className="text-green-700">
                                  <strong>Company:</strong> {approvedPlacement.internship.company_name}
                                </p>
                                <p className="text-green-700">
                                  <strong>Position:</strong> {approvedPlacement.internship.position_title}
                                </p>
                                <p className="text-green-700">
                                  <strong>Compatibility Score:</strong> {approvedPlacement.compatibility_score}%
                                </p>
                                <p className="text-green-700">
                                  <strong>Placed:</strong> {formatDateTime(approvedPlacement.placement_date)}
                                </p>
                              </>
                            )}
                          </div>
                        </div>
                      </Card>
                    );
                  })}
                </div>

                {/* Desktop Table */}
                <div className="hidden md:block overflow-x-auto">
                  <table className="w-full text-sm">
                    <thead>
                      <tr className="border-b">
                        <th className="text-left py-3 px-4 font-medium">Student Name</th>
                        <th className="text-left py-3 px-4 font-medium">Student Number</th>
                        <th className="text-left py-3 px-4 font-medium">Section</th>
                        <th className="text-left py-3 px-4 font-medium">Company</th>
                        <th className="text-left py-3 px-4 font-medium">Position</th>
                        <th className="text-left py-3 px-4 font-medium">Score</th>
                        <th className="text-left py-3 px-4 font-medium">Placed Date</th>
                        <th className="text-left py-3 px-4 font-medium">Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      {placedStudents.map((student) => {
                        const approvedPlacement = student.placements.find(p => p.status === 'approved');
                        return (
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
                            <td className="py-3 px-4 text-green-700">
                              {approvedPlacement?.internship.company_name || 'N/A'}
                            </td>
                            <td className="py-3 px-4 text-green-700">
                              {approvedPlacement?.internship.position_title || 'N/A'}
                            </td>
                            <td className="py-3 px-4 text-green-700">
                              {approvedPlacement?.compatibility_score || 'N/A'}%
                            </td>
                            <td className="py-3 px-4 text-gray-600">
                              {approvedPlacement ? formatDateTime(approvedPlacement.placement_date) : 'N/A'}
                            </td>
                            <td className="py-3 px-4">
                              <Badge className="bg-green-100 text-green-800 text-xs">
                                Successfully Placed
                              </Badge>
                            </td>
                          </tr>
                        );
                      })}
                    </tbody>
                  </table>
                </div>

                {/* Empty State */}
                {placedStudents.length === 0 && (
                  <div className="text-center py-8 text-gray-500">
                    <CheckCircleIcon className="h-12 w-12 mx-auto mb-4 text-gray-300" />
                    <p>No placed students for this season</p>
                  </div>
                )}
              </div>
            </CardContent>
          </Card>
        )}

        {/* Unplaced Students List */}
        {unplacedStudents.length > 0 && (
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <AlertTriangleIcon className="h-5 w-5 text-destructive" />
                Unplaced Students ({unplacedStudents.length})
              </CardTitle>
              <CardDescription>
                Students who could not be automatically placed due to no available slots
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {/* Mobile Cards */}
                <div className="block md:hidden space-y-3">
                  {unplacedStudents.map((unplaced) => (
                    <Card key={unplaced.id} className="p-4 border-destructive/50">
                      <div className="space-y-2">
                        <div className="flex items-center justify-between">
                          <h4 className="font-medium">
                            {unplaced.student.last_name}, {unplaced.student.first_name}
                            {unplaced.student.middle_name && ` ${unplaced.student.middle_name}`}
                          </h4>
                          <Badge variant="destructive" className="text-xs">
                            Unplaced
                          </Badge>
                        </div>
                        <div className="text-sm text-gray-600 space-y-1">
                          <p>Student Number: {unplaced.student.student_number}</p>
                          <p>Section: {unplaced.student.section.section_name}</p>
                          <p className="text-destructive">Reason: {unplaced.reason}</p>
                          <p>Flagged: {formatDateTime(unplaced.created_at)}</p>
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
                        <th className="text-left py-3 px-4 font-medium">Reason</th>
                        <th className="text-left py-3 px-4 font-medium">Flagged Date</th>
                        <th className="text-left py-3 px-4 font-medium">Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      {unplacedStudents.map((unplaced) => (
                        <tr key={unplaced.id} className="border-b">
                          <td className="py-3 px-4">
                            {unplaced.student.last_name}, {unplaced.student.first_name}
                            {unplaced.student.middle_name && ` ${unplaced.student.middle_name}`}
                          </td>
                          <td className="py-3 px-4 font-mono text-sm">
                            {unplaced.student.student_number}
                          </td>
                          <td className="py-3 px-4">
                            {unplaced.student.section.section_name}
                          </td>
                          <td className="py-3 px-4 text-destructive">
                            {unplaced.reason}
                          </td>
                          <td className="py-3 px-4 text-gray-600">
                            {formatDateTime(unplaced.created_at)}
                          </td>
                          <td className="py-3 px-4">
                            <Badge variant="destructive" className="text-xs">
                              Requires Manual Placement
                            </Badge>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
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
