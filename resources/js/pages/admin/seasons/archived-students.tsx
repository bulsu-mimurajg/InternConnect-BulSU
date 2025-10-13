import React from 'react';
import { Head, router } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { ArrowLeftIcon, ArchiveIcon, UsersIcon, CalendarIcon } from 'lucide-react';
import { format } from 'date-fns';
import AdminLayout from '@/layouts/admin/layout';

interface InternshipSeason {
  id: number;
  name: string;
  start_date: string;
  end_date: string;
  status: 'active' | 'completed' | 'archived';
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
  internship_season: InternshipSeason;
  is_active: boolean;
  created_at: string;
  updated_at: string;
}

interface Props {
  season: InternshipSeason;
  archivedStudents: Student[];
}

export default function ArchivedStudents({ season, archivedStudents }: Props) {
  const formatDate = (dateString: string) => {
    return format(new Date(dateString), 'MMM dd, yyyy');
  };

  const formatDateTime = (dateString: string) => {
    return format(new Date(dateString), 'MMM dd, yyyy HH:mm');
  };

  return (
    <AdminLayout>
      <Head title={`${season.name} - Archived Students`} />
      
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
              <h1 className="text-2xl font-bold text-gray-900">Archived Students</h1>
              <p className="text-gray-600">{season.name}</p>
            </div>
          </div>
          <Badge variant="secondary" className="flex items-center gap-2">
            <ArchiveIcon className="h-4 w-4" />
            {archivedStudents.length} Archived
          </Badge>
        </div>

        {/* Season Info */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <CalendarIcon className="h-5 w-5" />
              Season Information
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <p className="text-sm font-medium text-gray-500">Season Name</p>
                <p className="text-lg font-semibold">{season.name}</p>
              </div>
              <div>
                <p className="text-sm font-medium text-gray-500">Duration</p>
                <p className="text-lg font-semibold">
                  {formatDate(season.start_date)} - {formatDate(season.end_date)}
                </p>
              </div>
              <div>
                <p className="text-sm font-medium text-gray-500">Status</p>
                <Badge className={
                  season.status === 'active' ? 'bg-green-100 text-green-800' :
                  season.status === 'completed' ? 'bg-blue-100 text-blue-800' :
                  'bg-gray-100 text-gray-800'
                }>
                  {season.status === 'active' ? '🟢 Active' :
                   season.status === 'completed' ? '✅ Completed' :
                   '📁 Archived'}
                </Badge>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Archived Students */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <UsersIcon className="h-5 w-5" />
              Archived Students ({archivedStudents.length})
            </CardTitle>
            <CardDescription>
              Students who have been archived for this season. Their data is preserved but they are no longer active.
            </CardDescription>
          </CardHeader>
          <CardContent>
            {archivedStudents.length > 0 ? (
              <div className="space-y-4">
                {/* Mobile Cards */}
                <div className="block md:hidden space-y-3">
                  {archivedStudents.map((student) => (
                    <Card key={student.id} className="p-4">
                      <div className="space-y-3">
                        <div className="flex items-start justify-between">
                          <div>
                            <h4 className="font-medium text-lg">
                              {student.last_name}, {student.first_name}
                              {student.middle_name && ` ${student.middle_name}`}
                            </h4>
                            <p className="text-sm text-gray-600 font-mono">
                              {student.student_number}
                            </p>
                          </div>
                          <Badge variant="secondary" className="text-xs">
                            Archived
                          </Badge>
                        </div>
                        
                        <div className="grid grid-cols-2 gap-4 text-sm">
                          <div>
                            <p className="text-gray-500">Section</p>
                            <p className="font-medium">{student.section.section_name}</p>
                          </div>
                          <div>
                            <p className="text-gray-500">Archived</p>
                            <p className="font-medium">{formatDate(student.updated_at)}</p>
                          </div>
                        </div>

                        <div className="pt-2 border-t">
                          <p className="text-xs text-gray-500">
                            Originally registered: {formatDateTime(student.created_at)}
                          </p>
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
                        <th className="text-left py-3 px-4 font-medium">Registered</th>
                        <th className="text-left py-3 px-4 font-medium">Archived</th>
                        <th className="text-left py-3 px-4 font-medium">Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      {archivedStudents.map((student) => (
                        <tr key={student.id} className="border-b hover:bg-gray-50">
                          <td className="py-3 px-4">
                            <div>
                              <div className="font-medium">
                                {student.last_name}, {student.first_name}
                                {student.middle_name && ` ${student.middle_name}`}
                              </div>
                            </div>
                          </td>
                          <td className="py-3 px-4">
                            <span className="font-mono text-sm bg-gray-100 px-2 py-1 rounded">
                              {student.student_number}
                            </span>
                          </td>
                          <td className="py-3 px-4">
                            <Badge variant="outline" className="text-xs">
                              {student.section.section_name}
                            </Badge>
                          </td>
                          <td className="py-3 px-4 text-gray-600">
                            {formatDate(student.created_at)}
                          </td>
                          <td className="py-3 px-4 text-gray-600">
                            {formatDate(student.updated_at)}
                          </td>
                          <td className="py-3 px-4">
                            <Badge variant="secondary" className="text-xs">
                              <ArchiveIcon className="h-3 w-3 mr-1" />
                              Archived
                            </Badge>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>
            ) : (
              <div className="text-center py-12">
                <ArchiveIcon className="h-16 w-16 mx-auto mb-4 text-gray-300" />
                <h3 className="text-lg font-medium text-gray-900 mb-2">No Archived Students</h3>
                <p className="text-gray-600">
                  No students have been archived for this season yet.
                </p>
              </div>
            )}
          </CardContent>
        </Card>

        {/* Actions */}
        <div className="flex flex-col sm:flex-row gap-4">
          <Button
            variant="outline"
            onClick={() => router.get(`/admin/seasons/${season.id}/stats`)}
            className="flex items-center gap-2"
          >
            <CalendarIcon className="h-4 w-4" />
            View Season Statistics
          </Button>
          
          <Button
            variant="outline"
            onClick={() => router.get('/admin/seasons')}
            className="flex items-center gap-2"
          >
            <ArrowLeftIcon className="h-4 w-4" />
            Back to Seasons
          </Button>
        </div>
      </div>
    </AdminLayout>
  );
}
