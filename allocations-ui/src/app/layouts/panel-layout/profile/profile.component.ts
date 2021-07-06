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
import { Component, OnInit, OnDestroy } from '@angular/core';
import { FormBuilder, FormGroup, Validators, FormControl } from '@angular/forms';
import { noop, Subscription } from 'rxjs';
import { finalize } from 'rxjs/operators';
import { SnackbarService, ProfileService } from '../../../_services';
import { Title } from '@angular/platform-browser';
import * as _ from 'lodash';

interface InstancetData {
  name: string;
  ipAddress: string;
  port: string;
  username: string;
  password: string;
  wifiDefaultPassword: string;
  lanIpAddress: string;
  destinationUrl: string;
}

@Component({
  selector: 'app-profile',
  templateUrl: './profile.component.html',
  styleUrls: ['./profile.component.scss']
})
export class ProfileComponent implements OnInit, OnDestroy {

  public isFormLoading: boolean = false;
  public form: FormGroup;
  private submitted = false;
  private subscriptions: Subscription[] = [];
  public cardTitle: string = 'Add New Profile';
  public profiles: object[] = [];

  constructor(
    private formBuilder: FormBuilder,
    private profileServie: ProfileService,
    private snackBar: SnackbarService,
    private titleService: Title) { }

  ngOnInit(): void {
    this.titleService.setTitle('Add New Profile');
    this.initForm();
  }

  private initForm() {
    this.form = this.formBuilder.group({
      name: new FormControl(null, {
        validators: [
          Validators.required,
          Validators.maxLength(35),
          Validators.minLength(3),
          Validators.pattern(/^[ a-zA-Z0-9]+$/)
        ]
      }),
      sharedUsers: new FormControl(null, {
        validators: [
          Validators.required
        ]
      }),
      rateLimit: new FormControl(null, {
        validators: [
          Validators.required
        ]
      }),
      sessionTimeOut: new FormControl(null, {
        validators: [
          Validators.required
        ]
      })
    });
  }

  get f() {
    return this.form.controls;
  }

  onSubmit() {
    this.submitted = true;

    if (this.form.invalid) {
      return;
    }

    this.isFormLoading = true;

    this.subscriptions.push(this.profileServie.saveProfile(this.form.value)
      .pipe(finalize(() => this.isFormLoading = false))
      .subscribe((response: any) => {
        if (response) {
          this.snackBar.show(response.message, response.status ? 'success' : 'danger');
        }
      }, () => noop()));
  }

  ngOnDestroy() {
    this.subscriptions.forEach(subscription => subscription.unsubscribe());
  }
}
