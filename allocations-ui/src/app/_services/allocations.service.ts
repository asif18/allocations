/**
 * Copyrights Allocations 2021. All rights reserved
 *
 * The code, text and other elements of this application/file is copyrighted
 * You may not remove any copyright or other proprietary notices contained in this file
 * The rights granted to you use this application in your organization for your
 * business/personal purpose and not to sell or modify
 *
 * Developed by: Mohamed Asif
 * Email: mohamedasif18@gmail.com
 */
import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';
import { DefaultListApiParams, DefaultApiResponse } from '../_models';

@Injectable({
  providedIn: 'root'
})
export class AllocationsService {

  constructor(private http: HttpClient) { }

  getAllocationStatuses(postData: DefaultListApiParams ) {
    return this.http.post<any>(`${environment.apiUrl}/allocations/getAllocationStatuses`, postData);
  }

  getAllTos(): object[] {
    return [
      { code: 'BNSF', name: 'BNSF'  },
      { code: 'ICTF', name: 'ICTF'  }
    ];
  }

  saveAllocation(postData: any): Observable<any> {
    return this.http.post<any>(`${environment.apiUrl}/allocations/saveAllocation`, postData);
  }

  getAllocations(postData: DefaultListApiParams ) {
    return this.http.post<any>(`${environment.apiUrl}/allocations/getAllocations`, postData);
  }
  
  getAllocation(id: string) {
    return this.http.get<any>(`${environment.apiUrl}/allocations/getAllocation/${id}`);
  }

  exportAllocations(postData: DefaultListApiParams): Observable<any> {
    return this.http.post(`${environment.apiUrl}/allocations/getAllocations/export`, postData,
      {observe: 'response', responseType: 'blob'});
  }

  removeAllocation(id: string): Observable<any> {
    return this.http.get<any>(`${environment.apiUrl}/allocations/removeAllocation/${id}`);
  }

  allocate(postData: any): Observable<any> {
    return this.http.post<any>(`${environment.apiUrl}/allocations/allocate`, postData);
  }

  markAsDelivered(postData: any): Observable<any> {
    return this.http.post<any>(`${environment.apiUrl}/allocations/markAsDelivered`, postData);
  }

  markAsNotAllocated(postData: any): Observable<any> {
    return this.http.post<any>(`${environment.apiUrl}/allocations/markAsNotAllocated`, postData);
  }
}
