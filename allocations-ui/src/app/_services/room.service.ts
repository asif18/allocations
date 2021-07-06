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

@Injectable({
  providedIn: 'root'
})
export class RoomService {

  constructor(private http: HttpClient) { }

  addRoom(postData: any): Observable<any> {
    return this.http.post<any>(`${environment.apiUrl}/room/addRoom`, postData);
  }

  removeRoom(id: string): Observable<any> {
    return this.http.get<any>(`${environment.apiUrl}/room/removeRoom/${id}`);
  }

  checkOutRoom(id: string): Observable<any> {
    return this.http.get<any>(`${environment.apiUrl}/room/checkOutRoom/${id}`);
  }

  getRooms() {
    return this.http.get<any>(`${environment.apiUrl}/room/getRooms`);
  }
}
